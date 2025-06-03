<?php

namespace Webkul\ProductImportQueue\Model\Product\Attribute;

use Magento\Eav\Api\Data\AttributeInterface;

class Repository extends \Magento\Catalog\Model\Product\Attribute\Repository
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        switch ($attribute->getFrontendInput()) {
            case "visualswatch":
                $attribute->setFrontendInput('select');
                $attribute->setSwatchInputType('visual');
                break;
            case "textswatch":
                $attribute->setFrontendInput('select');
                $attribute->setSwatchInputType('text');
                break;
            case "select":
                $attribute->setFrontendInput('select');
                $attribute->setSwatchInputType('dropdown');
                break;
        }

        $attribute->setEntityTypeId(
            $this->eavConfig
                ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
                ->getId()
        );
        if ($attribute->getAttributeId()) {
            $existingModel = $this->get($attribute->getAttributeCode());

            if (!$existingModel->getAttributeId()) {
                throw NoSuchEntityException::singleField('attribute_code', $existingModel->getAttributeCode());
            }

            // Attribute code must not be changed after attribute creation
            $attribute->setAttributeCode($existingModel->getAttributeCode());
            $attribute->setAttributeId($existingModel->getAttributeId());
            $attribute->setIsUserDefined($existingModel->getIsUserDefined());
            $attribute->setFrontendInput($existingModel->getFrontendInput());

            $this->updateDefaultFrontendLabel($attribute, $existingModel);
        } else {
            $attribute->setAttributeId(null);

            if (!$attribute->getFrontendLabels() && !$attribute->getDefaultFrontendLabel()) {
                throw InputException::requiredField('frontend_label');
            }

            $frontendLabel = $this->updateDefaultFrontendLabel($attribute, null);

            $attribute->setAttributeCode(
                $attribute->getAttributeCode() ?: $this->generateCode($frontendLabel)
            );
            $this->validateCode($attribute->getAttributeCode());
            $this->validateFrontendInput($attribute->getFrontendInput());

            $attribute->setBackendType(
                $attribute->getBackendTypeByInput($attribute->getFrontendInput())
            );
            $attribute->setSourceModel(
                $this->productHelper->getAttributeSourceModelByInputType($attribute->getFrontendInput())
            );
            $attribute->setBackendModel(
                $this->productHelper->getAttributeBackendModelByInputType($attribute->getFrontendInput())
            );
            $attribute->setIsUserDefined(1);
        }
        if (!empty($attribute->getData(AttributeInterface::OPTIONS))) {
            $options = [];
            $sortOrder = 0;
            $default = [];
            $optionIndex = 0;
            foreach ($attribute->getOptions() as $option) {
                $optionIndex++;
                $optionId = $option->getValue() ?: 'option_' . $optionIndex;
                $options['value'][$optionId][0] = $option->getLabel();
                $options['order'][$optionId] = $option->getSortOrder() ?: $sortOrder++;
                if (is_array($option->getStoreLabels())) {
                    foreach ($option->getStoreLabels() as $label) {
                        $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
                    }
                }
                if ($option->getIsDefault()) {
                    $default[] = $optionId;
                }
            }
            $attribute->setDefault($default);
            if (count($options)) {
                $attribute->setOption($options);
            }
        }
        $this->attributeResource->save($attribute);
        return $this->get($attribute->getAttributeCode());
    }

    /**
     * This method sets default frontend value using given default frontend value or frontend value from admin store
     * if default frontend value is not presented.
     * If both default frontend label and admin store frontend label are not given it throws exception
     * for attribute creation process or sets existing attribute value for attribute update action.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface|null $existingModel
     * @return string|null
     * @throws InputException
     */
    private function updateDefaultFrontendLabel($attribute, $existingModel)
    {
        $frontendLabel = $attribute->getDefaultFrontendLabel();
        if (empty($frontendLabel)) {
            $frontendLabel = $this->extractAdminStoreFrontendLabel($attribute);
            if (empty($frontendLabel)) {
                if ($existingModel) {
                    $frontendLabel = $existingModel->getDefaultFrontendLabel();
                } else {
                    throw InputException::invalidFieldValue('frontend_label', null);
                }
            }
            $attribute->setDefaultFrontendLabel($frontendLabel);
        }
        return $frontendLabel;
    }


    /**
     * This method extracts frontend label from FrontendLabel object for admin store.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return string|null
     */
    private function extractAdminStoreFrontendLabel($attribute)
    {
        $frontendLabel = [];
        $frontendLabels = $attribute->getFrontendLabels();
        if (isset($frontendLabels[0])
            && $frontendLabels[0] instanceof \Magento\Eav\Api\Data\AttributeFrontendLabelInterface
        ) {
            foreach ($attribute->getFrontendLabels() as $label) {
                $frontendLabel[$label->getStoreId()] = $label->getLabel();
            }
        }
        return $frontendLabel[0] ?? null;
    }
}
