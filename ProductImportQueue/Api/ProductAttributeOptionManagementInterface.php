<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Webkul\ProductImportQueue\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ProductAttributeOptionManagementInterface
{
    /**
     * Add option to attribute
     *
     * @param string $attributeCode
     * @param \Webkul\ProductImportQueue\Api\Data\AttributeOptionInterface $option
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @return string
     */
    public function add($attributeCode, $option);

    /**
     * Update option to attribute
     *
     * @param string $attributeCode
     * @param \Webkul\ProductImportQueue\Api\Data\AttributeOptionInterface $option
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @return string
     */
    public function update($attributeCode, $option);
}
