<?php

namespace Webkul\ProductImportQueue\Model;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap;
use Magento\Catalog\Model\ProductRepository\MediaGalleryProcessor;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Entity\Attribute\Exception as AttributeException;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException as TemporaryCouldNotSaveException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\App\ResourceConnection;
use \Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;

/**
 * Product Repository.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductRepository implements \Webkul\ProductImportQueue\Api\ProductRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Product[]
     */
    protected $instances = [];

    /**
     * @var Product[]
     */
    protected $instancesById = [];

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceModel;

    /**
     * @var Product\Initialization\Helper\ProductLinks
     */
    protected $linkInitializer;

    /**
     * @var Product\LinkTypeProvider
     */
    protected $linkTypeProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $metadataService;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @deprecated 103.0.2
     *
     * @var ImageContentInterfaceFactory
     */
    protected $contentFactory;

    /**
     * @deprecated 103.0.2
     *
     * @var ImageProcessorInterface
     */
    protected $imageProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @deprecated 103.0.2
     *
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
     */
    protected $mediaGalleryProcessor;

    /**
     * @var MediaGalleryProcessor
     */
    private $mediaProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var ReadExtensions
     */
    private $readExtensions;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $linkManagement;

     /**
     * @var Request
     */
    private $_request;

     /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\Product\Option\Converter
     */
    protected $optionConverter;

    /**
     * @var AttributeRepository
     */
    protected $productAttributeRepository;
    

    /**
     * Undocumented function
     *
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceModel
     * @param \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer
     * @param \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Catalog\Model\Product\Option\Converter $optionConverter
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     * @param ImageContentInterfaceFactory $contentFactory
     * @param MimeTypeExtensionMap $mimeTypeExtensionMap
     * @param ImageProcessorInterface $imageProcessor
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param integer $cacheLimit
     * @param ReadExtensions|null $readExtensions
     * @param CategoryLinkManagementInterface|null $linkManagement
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Catalog\Model\Product\Option\Converter $optionConverter,
        \Magento\Framework\Filesystem $fileSystem,
        ImageContentValidatorInterface $contentValidator,
        ImageContentInterfaceFactory $contentFactory,
        MimeTypeExtensionMap $mimeTypeExtensionMap,
        AttributeRepository $productAttributeRepository,
        ImageProcessorInterface $imageProcessor,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Framework\Webapi\Rest\Request $request,
        ResourceConnection $resourceConnection,
        CollectionProcessorInterface $collectionProcessor = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        $cacheLimit = 1000,
        ReadExtensions $readExtensions = null,
        CategoryLinkManagementInterface $linkManagement = null
    ) {
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->initializationHelper = $initializationHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->linkInitializer = $linkInitializer;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->linkTypeProvider = $linkTypeProvider;
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->filterBuilder = $filterBuilder;
        $this->metadataService = $metadataServiceInterface;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->fileSystem = $fileSystem;
        $this->contentFactory = $contentFactory;
        $this->imageProcessor = $imageProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->cacheLimit = (int)$cacheLimit;
        $this->readExtensions = $readExtensions ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ReadExtensions::class);
        $this->linkManagement = $linkManagement ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(CategoryLinkManagementInterface::class);
        $this->_request = $request;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function get($sku, $useDefaultValues, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        $cachedProduct = $this->getProductFromLocalCache($sku, $cacheKey);
        if ($cachedProduct === null || $forceReload) {
            $product = $this->productFactory->create();

            $productId = $this->resourceModel->getIdBySku($sku);
            if (!$productId) {
                throw new NoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                );
            }
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            $this->cacheProduct($cacheKey, $product);
            $cachedProduct = $product;
        }



        return $cachedProduct;
    }

    /**
     * @inheritdoc
     */
    public function getById($productId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instancesById[$productId][$cacheKey]) || $forceReload) {
            $product = $this->productFactory->create();
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            if (!$product->getId()) {
                throw new NoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                );
            }
            $this->cacheProduct($cacheKey, $product);
        }
        return $this->instancesById[$productId][$cacheKey];
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * Add product to internal cache and truncate cache if it has more than cacheLimit elements.
     *
     * @param string $cacheKey
     * @param ProductInterface $product
     * @return void
     */
    private function cacheProduct($cacheKey, ProductInterface $product)
    {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->saveProductInLocalCache($product, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }

    /**
     * Merge data from DB and updates from request
     *
     * @param array $productData
     * @param bool $createNew
     * @return ProductInterface|Product
     * @throws NoSuchEntityException
     */
    protected function initializeProductData(array $productData, $createNew)
    {
        unset($productData['media_gallery']);
        if ($createNew) {
            $product = $this->productFactory->create();
            $this->assignProductToWebsites($product);
        } elseif (!empty($productData['id'])) {
            $this->removeProductFromLocalCacheById($productData['id']);
            $product = $this->getById($productData['id']);
        } else {
            $this->removeProductFromLocalCacheBySku($productData['sku']);
            $product = $this->get($productData['sku']);
        }

        foreach ($productData as $key => $value) {
            $product->setData($key, $value);
        }

        return $product;
    }

    /**
     * Assign product to websites.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    private function assignProductToWebsites(\Magento\Catalog\Model\Product $product)
    {
        if ($this->storeManager->getStore(true)->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
            $websiteIds = array_keys($this->storeManager->getWebsites());
        } else {
            $websiteIds = [$this->storeManager->getStore()->getWebsiteId()];
        }

        $product->setWebsiteIds($websiteIds);
    }

    /**
     * Process new gallery media entry.
     *
     * @deprecated 103.0.2
     * @see MediaGalleryProcessor::processNewMediaGalleryEntry()
     *
     * @param ProductInterface $product
     * @param array $newEntry
     * @return $this
     * @throws InputException
     * @throws StateException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processNewMediaGalleryEntry(
        ProductInterface $product,
        array  $newEntry
    ) {
        $this->getMediaGalleryProcessor()->processNewMediaGalleryEntry($product, $newEntry);

        return $this;
    }

    /**
     * Process product links, creating new links, updating and deleting existing links
     *
     * @param ProductInterface $product
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface[] $newLinks
     * @return $this
     * @throws NoSuchEntityException
     */
    private function processLinks(ProductInterface $product, $newLinks)
    {
        if ($newLinks === null) {
            // If product links were not specified, don't do anything
            return $this;
        }

        // Clear all existing product links and then set the ones we want
        $linkTypes = $this->linkTypeProvider->getLinkTypes();
        foreach (array_keys($linkTypes) as $typeName) {
            $this->linkInitializer->initializeLinks($product, [$typeName => []]);
        }

        // Set each linktype info
        if (!empty($newLinks)) {
            $productLinks = [];
            foreach ($newLinks as $link) {
                $productLinks[$link->getLinkType()][] = $link;
            }

            foreach ($productLinks as $type => $linksByType) {
                $assignedSkuList = [];
                /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $link */
                foreach ($linksByType as $link) {
                    $assignedSkuList[] = $link->getLinkedProductSku();
                }
                $linkedProductIds = $this->resourceModel->getProductsIdsBySkus($assignedSkuList);

                $linksToInitialize = [];
                foreach ($linksByType as $link) {
                    $linkDataArray = $this->extensibleDataObjectConverter
                        ->toNestedArray($link, [], \Magento\Catalog\Api\Data\ProductLinkInterface::class);
                    $linkedSku = $link->getLinkedProductSku();
                    if (!isset($linkedProductIds[$linkedSku])) {
                        throw new NoSuchEntityException(
                            __('The Product with the "%1" SKU doesn\'t exist.', $linkedSku)
                        );
                    }
                    $linkDataArray['product_id'] = $linkedProductIds[$linkedSku];
                    $linksToInitialize[$linkedProductIds[$linkedSku]] = $linkDataArray;
                }

                $this->linkInitializer->initializeLinks($product, [$type => $linksToInitialize]);
            }
        }

        $product->setProductLinks($newLinks);
        return $this;
    }

    /**
     * Process Media gallery data before save product.
     *
     * Compare Media Gallery Entries Data with existing Media Gallery
     * * If Media entry has not value_id set it as new
     * * If Existing entry 'value_id' absent in Media Gallery set 'removed' flag
     * * Merge Existing and new media gallery
     *
     * @param ProductInterface $product contains only existing media gallery items
     * @param array $mediaGalleryEntries array which contains all media gallery items
     * @return $this
     * @throws InputException
     * @throws StateException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processMediaGallery(ProductInterface $product, $mediaGalleryEntries)
    {
        $this->getMediaGalleryProcessor()->processMediaGallery($product, $mediaGalleryEntries);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function save(ProductInterface $product,$useDefaultVaules, $saveOptions = false)
    {
        $collection = $this->collectionFactory->create();
        $url_param = $this->_request->getParams();
        $requestProductData = $product->getData();
        
        $assignToCategories = false;
        $tierPrices = $product->getData('tier_price');
      
        try {
            $existingProduct = $product->getId() ? $this->getById($product->getId()) : $this->get($product->getSku(),$useDefaultVaules);
            
            if (isset($url_param['isDeleteExisingTierPrice'])) {
                if ($url_param['isDeleteExisingTierPrice'] == "true") {
                    $connection = $this->resourceConnection->getConnection();
                    $table = $collection->getTable('catalog_product_entity_tier_price');
                    $connection->query("DELETE FROM ".$table." where entity_id = ".$existingProduct->getId());
                }
            }
            
            $product->setData(
                $this->resourceModel->getLinkField(),
                $existingProduct->getData($this->resourceModel->getLinkField())
            );
            if (!$product->hasData(\Magento\Catalog\Model\Product::STATUS)) {
                $product->setStatus($existingProduct->getStatus());
            }

            /** @var ProductExtension $extensionAttributes */
            $extensionAttributes = $product->getExtensionAttributes();
            if (empty($extensionAttributes->__toArray())) {
                $product->setExtensionAttributes($existingProduct->getExtensionAttributes());
                $assignToCategories = true;
            }
        } catch (NoSuchEntityException $e) {
            $existingProduct = null;
        }

        $productDataArray = $this->extensibleDataObjectConverter
            ->toNestedArray($product, [], ProductInterface::class);
        $productDataArray = array_replace($productDataArray, $product->getData());
        $ignoreLinksFlag = $product->getData('ignore_links_flag');
        $productLinks = null;
        if (!$ignoreLinksFlag && $ignoreLinksFlag !== null) {
            $productLinks = $product->getProductLinks();
        }
        
        $productDataArray['store_id'] = (int)$this->storeManager->getStore()->getId();
        $product = $this->initializeProductData($productDataArray, empty($existingProduct));

        $this->processLinks($product, $productLinks);
        if (isset($productDataArray['media_gallery'])) {
            $loadedProduct = $this->productFactory->create()->load($product->getId());
            if ($loadedProduct && $loadedProduct->getId()) {
                $productimages = $loadedProduct->getMediaGalleryImages();
                $imagepath = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath().'catalog/product';                
                foreach($productimages as $productimage)
                {
                    try {
                        unlink($imagepath.$productimage['file']);
                    } catch (\Exception $e) {
                    }
                }
            }

            foreach ($productDataArray['media_gallery']['images'] as &$temp) {
                if ($temp['media_type']=='image' 
                    && isset($temp['content']['data']['base64_encoded_data'])
                    && filter_var($temp['content']['data']['base64_encoded_data'], FILTER_VALIDATE_URL)
                ) {
                    $temp['content']['data']['base64_encoded_data'] = base64_encode(file_get_contents($temp['content']['data']['base64_encoded_data']));
                }
            }
            $this->processMediaGallery($product, $productDataArray['media_gallery']['images']);
        }

        if (!$product->getOptionsReadonly()) {
            $product->setCanSaveCustomOptions(true);
        }

        $validationResult = $this->resourceModel->validate($product);
        if (true !== $validationResult) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Invalid product data: %1', implode(',', $validationResult))
            );
        }

        
        
        if ($tierPrices !== null) {
            //$product->unsTirePrice(); 
            $product->setData('tier_price', $tierPrices);
        }

        $this->saveProduct($product);
        if ($assignToCategories === true && $product->getCategoryIds()) {
            $this->linkManagement->assignProductToCategories(
                $product->getSku(),
                $product->getCategoryIds()
            );
        }
        $this->removeProductFromLocalCacheBySku($product->getSku());
        $this->removeProductFromLocalCacheById($product->getId());


        if($useDefaultVaules){
            $productId = $product->getId();
            
            $connection = $this->resourceConnection->getConnection();
            $table =$collection->getTable('eav_attribute');
            $catalog_product_entity_varchar = $collection->getTable('catalog_product_entity_varchar');
            $catalog_product_entity_int = $collection->getTable('catalog_product_entity_int');
            $catalog_product_entity_decimal = $collection->getTable('catalog_product_entity_decimal');
            $catalog_product_entity_text = $collection->getTable('catalog_product_entity_text');
            $sid = (int)$this->storeManager->getStore()->getId() ;

            $sql_query =  "select attribute_code from {$table} left join {$catalog_product_entity_varchar} on {$table}.attribute_id={$catalog_product_entity_varchar}.attribute_id 
            left join {$catalog_product_entity_int} on {$table}.attribute_id={$catalog_product_entity_int}.attribute_id
            left join {$catalog_product_entity_decimal} on {$table}.attribute_id={$catalog_product_entity_decimal}.attribute_id 
            left join {$catalog_product_entity_text} on {$table}.attribute_id={$catalog_product_entity_text}.attribute_id 
            where {$table}.entity_type_id=4 and ({$catalog_product_entity_varchar}.store_id ={$sid} or {$catalog_product_entity_int}.store_id ={$sid}
            or {$catalog_product_entity_decimal}.store_id ={$sid}
            or {$catalog_product_entity_text}.store_id ={$sid})";

            $result = $connection->query(
               $sql_query
            )->fetchAll();

            $attrArray = array_column($result,'attribute_code');
            $removedAttrValues = [];
            
            $attributeValues = $requestProductData;
            // dd($attributeValues);
            foreach($attrArray as $attr){
               
                $isInArray = isset($attributeValues[$attr]);
             
                if(!$isInArray){
                    $attr_id=$this->productAttributeRepository->get($attr)->getAttributeId();
                    $connection->query("Delete From {$catalog_product_entity_varchar} where entity_id={$productId} and attribute_id={$attr_id} and store_id = {$sid}");

                    $connection->query("Delete From {$catalog_product_entity_int} where entity_id={$productId} and attribute_id={$attr_id} and store_id = {$sid}");

                    $connection->query("Delete From {$catalog_product_entity_decimal} where entity_id={$productId} and attribute_id={$attr_id} and store_id = {$sid}");

                    $connection->query("Delete From {$catalog_product_entity_text} where entity_id={$productId} and attribute_id={$attr_id} and store_id = {$sid}");
                }
            }
        }

        return $this->get($product->getSku(),$useDefaultVaules, false, $product->getStoreId());
    }

    /**
     * @inheritdoc
     */
    public function delete(ProductInterface $product)
    {
        $sku = $product->getSku();
        $productId = $product->getId();
        try {
            $this->removeProductFromLocalCacheBySku($product->getSku());
            $this->removeProductFromLocalCacheById($product->getId());
            $this->resourceModel->delete($product);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('The "%1" product couldn\'t be removed.', $sku),
                $e
            );
        }
        $this->removeProductFromLocalCacheBySku($sku);
        $this->removeProductFromLocalCacheById($productId);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($sku)
    {
        $product = $this->get($sku);
        return $this->delete($product);
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        $collection->addAttributeToSelect('*');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $collection->addCategoryIds();
        $this->addExtensionAttributes($collection);
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        foreach ($collection->getItems() as $product) {
            $this->cacheProduct(
                $this->getCacheKey(
                    [
                        false,
                        $product->getStoreId()
                    ]
                ),
                $product
            );
        }

        return $searchResult;
    }

    /**
     * Add extension attributes to loaded items.
     *
     * @param Collection $collection
     * @return Collection
     */
    private function addExtensionAttributes(Collection $collection) : Collection
    {
        foreach ($collection->getItems() as $item) {
            $this->readExtensions->execute($item);
        }
        return $collection;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated 102.0.0
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $categoryFilter = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ?: 'eq';

            if ($filter->getField() == 'category_id') {
                $categoryFilter[$conditionType][] = $filter->getValue();
                continue;
            }
            $fields[] = ['attribute' => $filter->getField(), $conditionType => $filter->getValue()];
        }

        if ($categoryFilter) {
            $collection->addCategoriesFilter($categoryFilter);
        }

        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }

    /**
     * Clean internal product cache
     *
     * @return void
     */
    public function cleanCache()
    {
        $this->instances = null;
        $this->instancesById = null;
    }

    /**
     * Retrieve media gallery processor.
     *
     * @return MediaGalleryProcessor
     */
    private function getMediaGalleryProcessor()
    {
        if (null === $this->mediaProcessor) {
            $this->mediaProcessor = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(MediaGalleryProcessor::class);
        }

        return $this->mediaProcessor;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor::class
            );
        }
        return $this->collectionProcessor;
    }

    /**
     * Gets product from the local cache by SKU.
     *
     * @param string $sku
     * @param string $cacheKey
     * @return Product|null
     */
    private function getProductFromLocalCache(string $sku, string $cacheKey)
    {
        $preparedSku = $this->prepareSku($sku);

        return $this->instances[$preparedSku][$cacheKey] ?? null;
    }

    /**
     * Removes product in the local cache by sku.
     *
     * @param string $sku
     * @return void
     */
    private function removeProductFromLocalCacheBySku(string $sku): void
    {
        $preparedSku = $this->prepareSku($sku);
        unset($this->instances[$preparedSku]);
    }

    /**
     * Removes product in the local cache by id.
     *
     * @param string|null $id
     * @return void
     */
    private function removeProductFromLocalCacheById(?string $id): void
    {
        unset($this->instancesById[$id]);
    }

    /**
     * Saves product in the local cache by sku.
     *
     * @param Product $product
     * @param string $cacheKey
     * @return void
     */
    private function saveProductInLocalCache(\Magento\Catalog\Model\Product $product, string $cacheKey): void
    {
        $preparedSku = $this->prepareSku($product->getSku());
        $this->instances[$preparedSku][$cacheKey] = $product;
    }

    /**
     * Converts SKU to lower case and trims.
     *
     * @param string $sku
     * @return string
     */
    private function prepareSku(string $sku): string
    {
        return mb_strtolower(trim($sku));
    }

    /**
     * Save product resource model.
     *
     * @param ProductInterface|Product $product
     * @throws TemporaryCouldNotSaveException
     * @throws InputException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function saveProduct($product): void
    {
        try {
            $this->removeProductFromLocalCacheBySku($product->getSku());
            $this->removeProductFromLocalCacheById($product->getId());
            $this->resourceModel->save($product);
        } catch (ConnectionException $exception) {
            throw new TemporaryCouldNotSaveException(
                __('Database connection error'),
                $exception,
                $exception->getCode()
            );
        } catch (DeadlockException $exception) {
            throw new TemporaryCouldNotSaveException(
                __('Database deadlock found when trying to get lock'),
                $exception,
                $exception->getCode()
            );
        } catch (LockWaitException $exception) {
            throw new TemporaryCouldNotSaveException(
                __('Database lock wait timeout exceeded'),
                $exception,
                $exception->getCode()
            );
        } catch (AttributeException $exception) {
            throw InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $product->getData($exception->getAttributeCode()),
                $exception
            );
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The product was unable to be saved. Please try again.'),
                $e
            );
        }
    }
}
