<?php

namespace Webkul\ProductImportQueue\Api\Data;

interface ProductAttributeInterface extends \Magento\Catalog\Api\Data\ProductAttributeInterface
{
    const SWATCH_INPUT_TYPE = 'swatch_input_type';

    /**
     * Retrieve attribute type
     *
     * @return string|null
     */
    public function getSwatchInputType();

    /**
     * Set attribute type
     *
     * @param string $type
     * @return $this
     */
    public function setSwatchInputType($type);
}
