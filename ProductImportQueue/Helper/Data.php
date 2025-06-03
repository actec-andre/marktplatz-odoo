<?php

/**
 * @category   Webkul
 * @package    Webkul_ProductImportQueue
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\ProductImportQueue\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resource = $resource;
    }

    /**
     * Get info about product by product SKU
     *
     * @param string $sku
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductDataBySku($sku, $editMode = false, $storeId = null, $forceReload = false) {
        try {
            return $this->productRepository->get($sku, $editMode, $storeId, $forceReload);
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * Get Product By Sku
     *
     * @param string $sku
     * @return void
     */
    public function getProductIdBySku($sku)
    {
        $productData = $this->getProductDataBySku($sku);
        $productId = "";
        if (!empty($productData)) {
            $productId = $productData->getId();
        }
        return $productId;
    }

    /**
     * Get Collection By Sku
     *
     * @param array $skus
     * @return void
     */
    public function getCollectionBySku($skus)
    {
        
       return $this->productCollectionFactory->create()
        ->addAttributeToSelect(['entity_id','sku'])
        ->addFieldToFilter('sku',['in' => $skus]);
       
    }
    
    public function getCataProdLinkColl($skus)
    {
        $collection = $this->productCollectionFactory->create();
        $catalopProLink = $collection->getTable('catalog_product_link');
        $joinConditions = 'cpl.product_id = e.entity_id';
        $getCollectionBySku = $this->getCollectionBySku($skus);
        $getCollectionBySku->getSelect()->join(
            ['cpl' => $catalopProLink],
            $joinConditions,
            []
        )->group('cpl.product_id');
        return $getCollectionBySku;
    }

    public function getCatBundProCollection($skus)
    {
        $collection = $this->productCollectionFactory->create();
        $catProBunSel = $collection->getTable('catalog_product_bundle_selection');
        $joinConditions = 'cpbs.parent_product_id = e.entity_id';
        $getCollectionBySku = $this->getCollectionBySku($skus);
        $getCollectionBySku->getSelect()->join(
            ['cpbs' => $catProBunSel],
            $joinConditions,
            []
        )->group('cpbs.product_id');
        return $getCollectionBySku;
    }

/**
 * Remove all attribute values of product for a particulat store
 *
 * @return void
 */
    public function removeStoreAttribute()
    {
        # code...
    }

}
