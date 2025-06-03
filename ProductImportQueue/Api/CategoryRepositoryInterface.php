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
interface CategoryRepositoryInterface
{
    /**
     * Create category service
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\Catalog\Api\Data\CategoryInterface $category);
}
