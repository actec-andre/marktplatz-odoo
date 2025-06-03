<?php
namespace Webkul\ProductImportQueue\Model\Plugin;

/**
 * Plugin model for Catalog Resource Attribute
 */
class EavAttribute extends \Magento\Swatches\Model\Plugin\EavAttribute
{
    const DEFAULT_STORE_ID = 0;

    /**
     * Create links for non existed swatch options
     *
     * @param array $optionsArray
     * @param array $attributeSavedOptions
     * @return void
     */
    protected function prepareOptionLinks(array $optionsArray, array $attributeSavedOptions)
    {
        $dependencyArray = [];
        if (is_array($optionsArray['value'])) {
            $optionCounter = 0;
            $options = array_column($attributeSavedOptions, 'value', 'label');
            foreach ($optionsArray['value'] as $baseOptionId => $labels) {
                $dependencyArray[$baseOptionId] = $attributeSavedOptions[$optionCounter]['value'];
                $optionCounter++;
                if (!$dependencyArray[$baseOptionId]) {
                    $dependencyArray[$baseOptionId] = $options[$labels[self::DEFAULT_STORE_ID]];
                }
            }
        }
        $this->dependencyArray = $dependencyArray;
    }
}
