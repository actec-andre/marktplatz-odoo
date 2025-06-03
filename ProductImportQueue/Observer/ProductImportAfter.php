<?php
/**
 * @category   Webkul
 * @package    Webkul_ProductImportQueue
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\ProductImportQueue\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\RequestInterface;

/**
 * Observer class.
 */
class ProductImportAfter implements ObserverInterface
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Webkul\ProductImportQueue\Model\Product\Gallery\Video\Processor
     */
    protected $videoGalleryProcessor;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Webkul\ProductImportQueue\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $storeResolver;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Resource
     */
    protected $resource;

    protected $request;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\ProductImportQueue\Model\Product\Gallery\Video\Processor $videoGalleryProcessor
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Webkul\ProductImportQueue\Logger\Logger $logger
     * @param \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\ProductImportQueue\Model\Product\Gallery\Video\Processor $videoGalleryProcessor,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Webkul\ProductImportQueue\Logger\Logger $logger,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        CategoryLinkManagementInterface $linkManagement,
        CollectionFactory $productCollectionFactory,
        ResourceConnection $resource,
        RequestInterface $request
    ) {
       $this->productFactory = $productFactory; 
       $this->videoGalleryProcessor = $videoGalleryProcessor;
       $this->productRepository = $productRepository;
       $this->logger = $logger;
       $this->storeResolver = $storeResolver;
       $this->linkManagement = $linkManagement;
       $this->productCollectionFactory = $productCollectionFactory;
       $this->resource = $resource;
       $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $allProductData = $observer->getBunch();
            $dataToUpdate = [];
            foreach ($allProductData as $productData) {
                if (isset($productData['wk_video_data'])) {
                    $storeId = !empty($productData['_store'])
                                ? $observer->getAdapter()->getStoreIdByCode($productData['_store'])
                                : Store::DEFAULT_STORE_ID;
                    if (isset($productData['store_view_code']) && $productData['store_view_code']) {
                        $storeId = $this->storeResolver->getStoreCodeToId($productData['store_view_code']);
                    }
                    $videoData = json_decode($productData['wk_video_data'], true);
                    foreach ($videoData as $data) {
                        $product = $this->productRepository->get($productData['sku']);
                        $product = $this->productFactory->create()->setStoreId($storeId)->load($product->getId());
                        $productimages = $product->getMediaGalleryImages();
                        $videoUrls = [];
                        foreach($productimages as $productimage)
                        {
                            if ($productimage['video_provider']=='youtube' && $productimage['video_url']) {
                                $videoUrls[] = $productimage['video_url'];
                            }
                        }
                        try {
                            if (!in_array($data['url'], $videoUrls)) {
                                $res=$observer->getAdapter()->getUploader()->move($data['preview_image']);
                                if (!$res['file']) {
                                    $this->logger->info($data['preview_image'].' file not found.'); 
                                } else {
                                    $videoData = [
                                        'video_id' => "WK_".$productData['sku']."_".random_int(10000,99999),
                                        'video_title' => $data['title'],
                                        'video_description' => $data['description'],
                                        'video_provider' => 'youtube',
                                        'video_metadata' => null,
                                        'video_url' => $data['url'],
                                        'media_type' => \Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter::MEDIA_TYPE_CODE,
                                    ];
                                    $videoData['file'] = $res['file'];
                                    if ($product->hasGalleryAttribute())
                                    {
                                        $this->videoGalleryProcessor->addVideo(
                                            $product,
                                            $videoData,
                                            explode(',', $data['role']),
                                            false,
                                            isset($data['disabled']) && $data['disabled']
                                        );
                                    }
                                    $product->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $this->logger->info($e->getMessage()); 
                            $this->logger->info($e->getTraceAsString()); 
                        }
                    }
                }
                
                if (isset($productData['show_from_product_page']) && $productData['show_from_product_page']) {
                    try {
                        $dataToUpdate = array_merge_recursive(
                            $dataToUpdate,
                            $this->hideFromProductPage($productData)  
                        );
                    }
                    catch (\Exception $e) {
                        $this->logger->info("hideFromProductPage: ".$e->getMessage()); 
                    }
                }
            }
            $this->updateHideFromProductPage($dataToUpdate);
            $skuArray = array_column($allProductData, 'sku');
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect(['entity_id', 'sku']);
            $productCollection->addAttributeToFilter('sku', ['in' => $skuArray]);
            $productIds = [];
            foreach ($productCollection as $product) {
                $productIds[$product->getSku()] = $product->getId();
            }
            $this->assignCategories($productIds, $allProductData);
            
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage()); 
            $this->logger->info($e->getTraceAsString()); 
        }
    }

    public function assignCategories($productIds, $allProductData) {
        try {
            $delimiter = ',';
            $connection = $this->resource->getConnection();
            $catalogCategoryProductTable = $this->resource->getTableName('catalog_category_product');
            $insertData = [];
            $proIds = [];
            foreach ($allProductData as $product) {
                if (isset($product['category_ids'])) {
                    $categoryIds = explode($delimiter, $product['category_ids']);
                    foreach ($categoryIds as $catId) {
                        $insertData[] = ['category_id' => $catId, 'product_id' => $productIds[$product['sku']]];
                    }
                    $proIds[] = $productIds[$product['sku']];
                }
            }
            if (!empty($insertData)) {
                $proIds = implode(',', $proIds);
                $sql = "DELETE FROM $catalogCategoryProductTable WHERE product_id IN ($proIds)";
                $connection->query($sql);
                $connection->insertMultiple($catalogCategoryProductTable, $insertData);
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage()); 
            $this->logger->info($e->getTraceAsString()); 
        }
    }

    public function hideFromProductPage($productData) {
        $dataToUpdate = [];
        try {
            $delimiter = ',';
            $product = $this->productRepository->get($productData['sku']);
            $isVisible = explode($delimiter, $productData['show_from_product_page']);
            $cdnImageUrls = explode($delimiter, $productData['additional_images']);
            $mediaGalleryEntries = $product->getMediaGalleryEntries();
            foreach ($mediaGalleryEntries as $entry) {
                if ($entry->getMediaType() == "image") {
                    foreach ($cdnImageUrls as $index => $cdnImageUrl) {
                        $tempName = explode('/', urldecode(urldecode($cdnImageUrl)));
                        $name = end($tempName);
                        $tempName = explode('.', $name);
                        $name = reset($tempName);
                        $matchImage = null;
                        if (preg_match('#' . preg_quote($name, '#') . '#i', $entry->getFile())) {
                            $matchImage = $entry->getFile();
                        }
    
                        if ($matchImage) {
                            $visible = strtolower($isVisible[$index] ?? '');
                            if ($visible == 'yes' || $visible == 'no') {
                                $disabledValue = $visible == 'yes' ? 0 : 1;
                                $dataToUpdate[] = [
                                    'value_id' => $entry->getId(),
                                    'disabled' => $disabledValue,
                                    'entity_id' => $product->getId()
                                ];
                            }
                            break; // Found a match, no need to check further CDN URLs
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            $this->logger->info($e->getTraceAsString());
        }
        return $dataToUpdate;
    }

    public function updateHideFromProductPage($data) {
        if (!empty($data)) {
            $connection = $this->resource->getConnection();
            $tableName = $connection->getTableName('catalog_product_entity_media_gallery_value');
            $mediaId = implode(',', array_column($data, 'value_id'));
            $sql = "DELETE FROM $tableName WHERE value_id IN ($mediaId)";
            $connection->query($sql);
            $columns = ['value_id', 'disabled', 'entity_id'];
            $connection->insertOnDuplicate(
                $tableName,
                $data,
                $columns
            );
        }
    }
}
