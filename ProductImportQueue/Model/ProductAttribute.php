<?php
namespace Webkul\ProductImportQueue\Model;

use Webkul\ProductImportQueue\Api\ProductAttributeInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface as AttributeSetInterface;
use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;

class ProductAttribute implements ProductAttributeInterface
{

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    private $eavResource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $joinProcessor;

    /**
     * @var \Magento\Eav\Api\AttributeGroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;

    /**
     * @var ProductAttributeManagementInterface
     */
    private $productAttributeManagementInterface;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepositoryInterface;

    /**
     * Dependeicies classes
     *
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param ProductAttributeManagementInterface $productAttributeManagementInterface
     * @param \Magento\Eav\Api\AttributeGroupRepositoryInterface $groupRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepositoryInterface
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        ProductAttributeManagementInterface $productAttributeManagementInterface,
        \Magento\Eav\Api\AttributeGroupRepositoryInterface $groupRepository,
        AttributeSetRepositoryInterface $attributeSetRepositoryInterface,
        \Magento\Framework\Webapi\Rest\Request $request,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavResource = $eavResource;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->attributeFactory = $attributeFactory;
        $this->joinProcessor = $joinProcessor;
        $this->groupRepository = $groupRepository;
        $this->request = $request;
        $this->productAttributeManagementInterface = $productAttributeManagementInterface;
        $this->attributeSetRepositoryInterface = $attributeSetRepositoryInterface;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

     /**
      * Get list of attributes
      *
      * @param string $entityTypeCode
      * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
      * @return array
      */
    public function getList($entityTypeCode, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        
        if (!$entityTypeCode) {
            throw InputException::requiredField('entity_type_code');
        }
        $attributeSetIds = $this->request->getParam('attributeSetId');

       // $attributeSetIds =$this->getAttributesSetList($searchCriteria);

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();
        $this->joinProcessor->process($attributeCollection);
        $attributeCollection->addFieldToFilter('entity_type_code', ['eq' => $entityTypeCode]);
        $attributeCollection->join(
            ['entity_type' => $attributeCollection->getTable('eav_entity_type')],
            'main_table.entity_type_id = entity_type.entity_type_id',
            []
        );
        $attributeCollection->joinLeft(
            ['eav_entity_attribute' => $attributeCollection->getTable('eav_entity_attribute')],
            'main_table.attribute_id = eav_entity_attribute.attribute_id',
            []
        );
        $entityType = $this->eavConfig->getEntityType($entityTypeCode);

        $additionalTable = $entityType->getAdditionalAttributeTable();
        if ($additionalTable) {
            $attributeCollection->join(
                ['additional_table' => $attributeCollection->getTable($additionalTable)],
                'main_table.attribute_id = additional_table.attribute_id',
                []
            );
        }

        $this->collectionProcessor->process($searchCriteria, $attributeCollection);

        // Group attributes by id to prevent duplicates with different attribute sets
        $attributeCollection->addAttributeGrouping();
        
        $attributeCollection->setAttributeSetFilter($attributeSetIds);

        $attributes = [];
        /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
        foreach ($attributeCollection as $attribute) {
            // $attributes[] = $this->get($entityTypeCode, $attribute->getAttributeCode());
             $attrObject = $this->get($entityTypeCode, $attribute->getAttributeCode());
             $attr = $this->get($entityTypeCode, $attribute->getAttributeCode())->getData();
             $attr['attribute_group_name'] = $this->getAttributeGroupName($attribute->getData('attribute_group_id'));
             $attr['frontend_labels'] = $attrObject->getFrontendLabels();
             $attr['default_frontend_label'] = $attrObject->getDefaultFrontendLabel();
             $attributes[] = json_decode(json_encode($attr), true);

        }

        /** @var \Magento\Eav\Api\Data\AttributeSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($attributes);

        // if $searchCriteria has no page size - we can use count() on $attributeCollection
        // otherwise - we have to use getSize() on $attributeCollection
        // with this approach we can eliminate excessive COUNT requests in case page size is empty
        if ($searchCriteria->getPageSize()) {
            $searchResults->setTotalCount($attributeCollection->getSize());
        } else {
            $searchResults->setTotalCount(count($attributeCollection));
        }

        return $searchResults;
    }
    /**
     * Get attribute by code
     *
     * @param string $entityTypeCode
     * @param string $attributeCode
     * @return \Magento\Eav\Api\Data\AttributeInterface $attribute
     */
    public function get($entityTypeCode, $attributeCode)
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
        $attribute = $this->eavConfig->getAttribute($entityTypeCode, $attributeCode);
        if (!$attribute || !$attribute->getAttributeId()) {
            throw new NoSuchEntityException(
                __(
                    'The attribute with a "%1" attributeCode doesn\'t exist. Verify the attribute and try again.',
                    $attributeCode
                )
            );
        }
        return $attribute;
    }
    /**
     * Get all attribbute
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface
     */
    public function getAttribute(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->getList(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );
    }

    /**
     * Get Attribute Set by attribute Id
     *
     * @param [type] $attributeId
     * @return AttributeSetInterface
     */
    public function getAttributeSet($attributeId)
    {
        try {
            $attributeSet = $this->attributeFactory->create()->load($attributeId);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $attributeSet;
    }

    /**
     * Get Attribute list by sets
     *
     * @return array
     */
    public function getAttributeListBySetId()
    {
        $attributeSetId = 4; // Default Attribute set
        try {
            $getAttributes = $this->productAttributeManagementInterface
                ->getAttributes($attributeSetId);
        } catch (NoSuchEntityException $exception) {
            throw new NoSuchEntityException(__($exception->getMessage()));
        }
        return $getAttributes;
    }

    /**
     * Get Attribute Group name by attribute group id
     *
     * @param string $groupId
     * @return string|null
     */
    public function getAttributeGroupName($groupId)
    {
        try {
            return  $this->groupRepository->get($groupId)->getAttributeGroupName();
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }

    /**
     * Get Attribute set list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\AttributeSetRepositoryInterface
     */
    public function getAttributesSetList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $attributeSetIds =[];
        try {
            $attributeSet = $this->attributeSetRepositoryInterface->getList($searchCriteria);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
        foreach ($attributeSet->getItems() as $key => $value) {
            array_push($attributeSetIds, $key);
        }
        /** @var \Magento\Eav\Api\Data\AttributeSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($attributeSetIds);

        // if $searchCriteria has no page size - we can use count() on $attributeCollection
        // otherwise - we have to use getSize() on $attributeCollection
        // with this approach we can eliminate excessive COUNT requests in case page size is empty
      
        $searchResults->setTotalCount(count($attributeSetIds));

        return $searchResults;
    }
    /**
     * Retrieve collection processor
     *
     * @deprecated 101.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                CollectionProcessor::class
            );
        }
        return $this->collectionProcessor;
    }
}
