<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ProductImportQueue
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ProductImportQueue\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ProductAttributeInterface
{
    /**
     * Undocumented function
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface
     */
    public function getAttribute(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Undocumented function
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\AttributeSetRepositoryInterface
     */
    public function getAttributesSetList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
