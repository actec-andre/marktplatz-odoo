<?php
namespace Webkul\ProductImportQueue\Model\Product\Attribute;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

/**
 * Option management model for product attribute.
 */
class OptionManagement implements \Webkul\ProductImportQueue\Api\ProductAttributeOptionManagementInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\OptionManagement
     */
    protected $optionManagement;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected $eavOptionManagement;

    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $resourceModel;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    protected $optionFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var \Magento\Swatches\Helper\Media
     */
    protected $mediaHelper;

    /**
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $eavOptionManagement
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Attribute\OptionManagement $optionManagement,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $resourceModel,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Eav\Api\AttributeOptionManagementInterface $eavOptionManagement,
        DirectoryList $directoryList,
        File $file,
        \Magento\Swatches\Helper\Media $mediaHelper
    ) {
        $this->optionManagement = $optionManagement;
        $this->eavOptionManagement = $eavOptionManagement;
        $this->attributeRepository = $attributeRepository;
        $this->resourceModel = $resourceModel;
        $this->optionFactory = $optionFactory;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * @inheritdoc
     */
    public function update($attributeCode, $option)
    {

        /** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $currentOptions */
        $currentOptions = $this->optionManagement->getItems($attributeCode);

        if (is_array($currentOptions)) {
            array_walk($currentOptions, function (&$attributeOption) {
                /** @var \Magento\Eav\Api\Data\AttributeOptionInterface $attributeOption */
                $attributeOption = $attributeOption->getLabel();
            });

            // if (in_array($option->getLabel(), $currentOptions, true)) {
            //     return false;
            // }
        }
        return $this->updateSave(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $option
        );
    }

    public function updateSave($entityType, $attributeCode, $option)
    {
        if (empty($attributeCode)) {
            throw new InputException(__('The attribute code is empty. Enter the code and try again.'));
        }

        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        if (!$attribute->usesSource()) {
            throw new StateException(__('The "%1" attribute doesn\'t work with options.', $attributeCode));
        }

        $optionLabel = $option->getLabel();
        $optionId = $this->getOptionId($option);
        if ($optionId == 'new_option') {
            $allOptions = $attribute->getSource()->getAllOptions(false, true);
            foreach ($allOptions as $optiondata) {
                if (strcasecmp($optiondata['label'], $optionLabel) == 0 || $optiondata['value'] == $optionLabel) {
                    $optionId = $optiondata['value'];
                }
            }
        }
        $this->validateOption($attribute, $optionId);

        $options = [];
        $options['value'][$optionId][0] = $optionLabel;
        $options['order'][$optionId] = $option->getSortOrder();

        if (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
            }
        }

        if ($option->getIsDefault()) {
            $attribute->setDefault([$optionId]);
        }
        $imageUrl = $option->getImageUrl();

        if ($imageUrl) {
            $imageUrl = str_replace("%2F", "%252F", $imageUrl);
        } else {
            $imageUrl = '';
        }


        if ($imageUrl != '') {
            $imageFilename = basename($imageUrl);
            $image_type = substr(strrchr($imageFilename, "."), 1);
            $filename = md5($imageUrl . strtotime('now')).'.'.$image_type;
            $tmpDir = $this->getMediaDirTmpDir();
            $filepath = $tmpDir . '/'. $filename;
            $ggh=file_get_contents(trim($imageUrl));
            $fp = fopen($filepath, "w");
            fwrite($fp, $ggh);
            fclose($fp);
            $newFile = $this->mediaHelper->moveImageFromTmp($filename);
            if (substr($newFile, 0, 1) == '.') {
                $newFile = substr($newFile, 1);
            }
            $this->mediaHelper->generateSwatchVariations($newFile);
            $swatchoption['value'][$optionId] = $newFile;
            $attribute->setSwatchvisual($swatchoption);
        } elseif ($option->getColorCode() != '') {
            $swatchoption['value'][$optionId] = $option->getColorCode();
            $attribute->setSwatchvisual($swatchoption);
        }

        $attribute->setOption($options);
        try {
            $this->resourceModel->save($attribute);
            if ($optionLabel != "" && $attribute->getAttributeCode()) {
                $this->setOptionValue($option, $attribute, $optionLabel);
            }
        } catch (\Exception $e) {
            throw new StateException(__('The "%1" attribute can\'t be saved.', $attributeCode));
        }

        return $optionId;
    }

    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp/catalog/product';
    }

    /**
     * Validate option
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param int $optionId
     * @throws NoSuchEntityException
     * @return void
     */
    private function validateOption($attribute, $optionId)
    {
        if ($attribute->getSource()->getOptionText($optionId) === false) {
            throw new NoSuchEntityException(
                __(
                    'The "%1" attribute doesn\'t include an option with "%2" ID.',
                    $attribute->getAttributeCode(),
                    $optionId
                )
            );
        }
    }

    /**
     * Returns option id
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @return string
     */
    private function getOptionId(\Magento\Eav\Api\Data\AttributeOptionInterface $option) : string
    {
        return $option->getValue() ?: 'new_option';
    }

    /**
     * Set option value
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param string $optionLabel
     * @return void
     */
    private function setOptionValue(
        \Magento\Eav\Api\Data\AttributeOptionInterface $option,
        \Magento\Eav\Api\Data\AttributeInterface $attribute,
        string $optionLabel
    ) {
        $optionId = $attribute->getSource()->getOptionId($optionLabel);
        if ($optionId) {
            $option->setValue($attribute->getSource()->getOptionId($optionId));
        } elseif (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                if ($optionId = $attribute->getSource()->getOptionId($label->getLabel())) {
                    $option->setValue($attribute->getSource()->getOptionId($optionId));
                    break;
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function add($attributeCode, $option)
    {
        /** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $currentOptions */
        $currentOptions = $this->optionManagement->getItems($attributeCode);

        if (is_array($currentOptions)) {
            array_walk($currentOptions, function (&$attributeOption) {
                /** @var \Magento\Eav\Api\Data\AttributeOptionInterface $attributeOption */
                $attributeOption = $attributeOption->getLabel();
            });

            if (in_array($option->getLabel(), $currentOptions, true)) {
                return false;
            }
        }
        return $this->addSave(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $option
        );
    }

    /**
     * @inheritdoc
     */
    public function addSave($entityType, $attributeCode, $option)
    {
        if (empty($attributeCode)) {
            throw new InputException(__('The attribute code is empty. Enter the code and try again.'));
        }
        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        if (!$attribute->usesSource()) {
            throw new StateException(__('The "%1" attribute doesn\'t work with options.', $attributeCode));
        }
        $optionLabel = $option->getLabel();
        $optionId = $this->getOptionId($option);
        if ($optionId == 'new_option') {
            $optionsData = $attribute->getSource()->getAllOptions(false, true);
            foreach ($optionsData as $optionData)
            {
                if ($optionLabel == $optionData['label']) {
                    $optionId = $optionData['value'];
                    break;
                }
            }
        }
        $options = [];
        $options['value'][$optionId][0] = $optionLabel;
        $options['order'][$optionId] = $option->getSortOrder();

        if (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
            }
        }

        if ($option->getIsDefault()) {
            $attribute->setDefault([$optionId]);
        }

        $attribute->setOption($options);
        try {
            $this->resourceModel->save($attribute);
            if ($optionLabel != "" && $attribute->getAttributeCode()) {
                $this->setOptionValue($option, $attribute, $optionLabel);
            }
        } catch (\Exception $e) {
            throw new StateException(__('The "%1" attribute can\'t be saved.', $attributeCode));
        }

        $optionId = $this->getOptionId($option);

        $this->updateSave($entityType, $attributeCode, $option);

        return $optionId;
    }
}
