<?php
namespace Webkul\ProductImportQueue\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ProductRepositoryInterface
{
    /**
     * Create product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param bool $saveOptions
     * @param bool $useDefaultVaules
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\Catalog\Api\Data\ProductInterface $product, $useDefaultVaules, $saveOptions = false);
}
