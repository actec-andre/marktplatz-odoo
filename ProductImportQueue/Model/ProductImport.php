<?php

/**
 * @category   Webkul
 * @package    Webkul_ProductImportQueue
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\ProductImportQueue\Model;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Webkul\ProductImportQueue\Helper\Data;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\Framework\App\Filesystem\DirectoryList;

class ProductImport implements \Webkul\ProductImportQueue\Api\ProductImportInterface
{

    public const OPERATION_TYPE = 'product';
    public const ENTITY_TYPE = 'catalog_product';
    public const FIELD_IMPORT_IDS = '_import_ids';
    
    /**
     * @var \FireGento\FastSimpleImport\Model\Importer
     */
    private $importerModel;

    /**
     * @var Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * @var \Webkul\ProductImportQueue\Logger\Logger $logger
     */
    private $logger;

    /**
     * @var Product\CategoryProcessor
     */
    private $categoryProcessor;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
     */
    private $imageProcessor;

    /**
     * @var string
     */
    private $pimMultiValueSeparator;

    /**
     * @var \Magento\Downloadable\Model\SampleFactory
     */
    private $sampleFactory;

    /**
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    private $linkFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var \Magento\ImportExport\Model\ImportFactory
     */
    private $importFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $directory;

    /**
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     * @param Product\CategoryProcessor $categoryProcessor
     * @param Curl $curl,
     * @param \Webkul\ProductImportQueue\Logger\Logger $logger,
     * @param \Magento\Catalog\Model\ProductRepository $productRepository,
     * @param \Magento\Catalog\Model\Product\Gallery\Processor $imageProcessor
     */
    public function __construct(
        // \FireGento\FastSimpleImport\Model\Importer $importerModel,
        Product\CategoryProcessor $categoryProcessor,
        ResourceConnection $resource,
        Curl $curl,
        \Webkul\ProductImportQueue\Logger\Logger $logger,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product\Gallery\Processor $imageProcessor,
        \Magento\Downloadable\Model\SampleFactory $sampleFactory,
        \Magento\Downloadable\Model\LinkFactory $linkFactory,
        Data $helper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        // $this->importerModel = $importerModel;
        $this->resource = $resource;
        $this->categoryProcessor = $categoryProcessor;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->imageProcessor = $imageProcessor;
        $this->sampleFactory = $sampleFactory;
        $this->linkFactory = $linkFactory;
        $this->helper = $helper;
        $this->formKey = $formKey;
        $this->importFactory = $importFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Process csv add dependency by ruuning command: composer require firegento/fastsimpleimport;
     *
     * @return Void
     */
    public function processCsv($data)
    {
        
        $response = [];
        $this->pimMultiValueSeparator = isset($data['multi_value_seperator']) ? $data['multi_value_seperator'] : ",";
        $paramsData = [
            'form_key' => $this->formKey->getFormKey(),
            'entity' => $data['entity'] ?? self::ENTITY_TYPE,
            'behavior' => $data['behavior'] ?? 'append',
            'validation_strategy' => 'validation-skip-errors',
            'allowed_error_count' => 10,
            '_import_field_separator' => $data['delimiter'] ?? ',',
            '_import_multiple_value_separator' => $this->pimMultiValueSeparator,
            'import_images_file_dir' => '',
            '_import_empty_attribute_value_constant' => '__EMPTY__VALUE__'
        ];
        if (isset($data['path'])) {
            try {
                $filePath = "import/catalog_product_import_".date('Y-m-d_H-i-s').".csv";
                $this->directory->create('import');
                $file = $this->directory->openFile($filePath, 'w+');
                try {
                    $file->lock();
                    $fileContent = $this->getCurlResponse($data['path']);
                    $file->write($fileContent);
                } finally {
                    $file->unlock();
                    $file->close();
                }
                $csvFullPath = $this->directory->getAbsolutePath($filePath);
                if (($handle = fopen($csvFullPath, 'r')) !== false) {
                    $delimiter = $data['delimiter'] ?? ';';
                    $enclosure = $data['enclosure'] ?? '"';
                    $multiValueSeparator = $data['multi_value_seperator'] ?? ',';
                    $rows = [];
                    $c = 0;
                    $globalIndex = -1;
                    $storeIndex = -1;
                    while (($row = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false) {
                        if($c == 0) {
                            $storeKey = array_search('store_view_code', $row);
                            $skuKey = array_search('sku', $row);
                            if ($storeKey !== false) {
                                $storeIndex = $storeKey;
                            }
                            $key = "url_key";
                            $index = array_search($key, $row);
                            if ($index !== false) {
                                $globalIndex = $index;
                            }
                        }
                        if ($storeIndex >=0 && $c > 0 && !$row[$storeIndex]) {
                            continue;
                        }
                        if ($globalIndex >=0 && $c > 0) {
                            $row[$globalIndex] = $row[$globalIndex] ? $row[$globalIndex]."-".$c : $row[$skuKey]."-".$c;
                        }
                        $rows[] = array_values($row);
                        $c+=1;
                    }
                    fclose($handle);
                    if ($this->directory->isExist($filePath)) {
                        $this->directory->delete($filePath);
                    }
                    $paramsData['_import_field_separator'] = ',';
                    $stream = $this->directory->openFile($filePath, 'w+');
                    $stream->lock();
                    foreach ($rows as $row) {
                        $stream->writeCsv($row);
                    }
                    $stream->unlock();
                    $stream->close();
                }
                $importModel = $this->importFactory->create();
                $importModel->setData($paramsData);
                $sourceModel = Adapter::findAdapterFor(
                    $filePath,
                    $this->directory,
                    $importModel->getData('_import_field_separator')
                );
                
                $errorAggregator = $importModel->getErrorAggregator();

                $isValid = $importModel->validateSource($sourceModel);
                
                // validation ids
                $ids = $importModel->getValidatedIds();
                $ids = !empty($ids) ? implode(',',$ids) : null;
                
                // collecting all validation errors
                $errors = $errorAggregator->getAllErrors();

                $invalidRowsCount = $errorAggregator->getInvalidRowsCount();

                /**
                 * clearing errors before import
                 * this is a work around for combining validation 
                 * and import a two part process from admin 
                 * making it work like single process
                **/
                $errorAggregator->clear();

                $importModel->setData(self::FIELD_IMPORT_IDS, $ids);

                $response['skipped_errors'] = [];
                $response['created'] = 0;
                $response['updated'] = 0;

                foreach($errors as $e){
                    $response['skipped_errors']["row-".$e->getRowNumber()] = $e->getErrorMessage();
                }
                
                if ($isValid && $importModel->getProcessedRowsCount() != $invalidRowsCount) {
                    if ($importModel->importSource()) {
                        $created = $importModel->getCreatedItemsCount();
                        $updated = $importModel->getUpdatedItemsCount();
                        $message = __(
                            'Products Created: %1, Updated: %2',
                            $created,
                            $updated
                        );
                        $response['msg'] = $message;
                        $response['created'] = $created;
                        $response['updated'] = $updated;
                    } else {
                        $response['error'] = $importModel->getFormatedLogTrace();
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical('Error Import :- '.$e->getMessage());
                $response['error'] =  $e->getMessage();
            }
            if ($this->directory->isExist($filePath)) {
                $this->directory->delete($filePath);
            }
        }
        return ['response' => $response];
    }

        /**
     * Process csv add dependency by ruuning command: composer require firegento/fastsimpleimport;
     *
     * @return Void
     */
    public function beforeProcessCsv($data)
    {
        $response = [];
        $this->pimMultiValueSeparator = isset($data['multi_value_seperator']) ? $data['multi_value_seperator'] : ",";

        try {
             $productsArray = $this->getEntities($data);
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        if (!isset($productsArray['error'])) {
            $this->deleteExtraCategories($productsArray);
            $this->deleteOptionsAndSelections($productsArray);
            $this->dropAllUnneededSelections($productsArray);
            $this->deleteAssociations($productsArray);
            $productsArray = $this->deletePreviousImages($productsArray);
        }
        return ['response' => $response];
    }

    /**
     * workaround for issue: https://github.com/magento/magento2/issues/15273
     * @param $productsArray Array of products to import
     */
    private function deleteExtraCategories($productsArray)
    {
        $collection = $this->linkFactory->create()->getCollection();
       foreach ($productsArray as $product) {
            if (empty($product['store_view_code']) || $product['store_view_code'] === 'all') {
                $categoryIds = [];
                if (!empty($product['categories'])) {
                    $categoryIds = $this->categoryProcessor->upsertCategories(
                        $product['categories'],
                        $this->pimMultiValueSeparator
                    );
                }
                $categoryNoDeleteIds = trim(implode(",", array_filter($categoryIds, 'is_numeric')), ' ');
                $categoryTable = $collection->getTable('catalog_category_product');
                $productTable  = $collection->getTable('catalog_product_entity');
                $deleteString = '
                    delete ccp from ' . $categoryTable . ' ccp left join ' .
                    $productTable . ' cpe on ccp.product_id = cpe.entity_id
                    where cpe.sku = "' . $product['sku'] . '" ';
                if (array_filter($categoryIds)) {
                    $deleteString .= ' and ccp.category_id not in (' . $categoryNoDeleteIds . ')';
                }
                $deleteString .= ';';
                $this->resource->getConnection()->query($deleteString);
                $categoriesIn =[];
                $productId = $this->helper->getProductIdBySku($product['sku']);
                
                if (!empty($productId)) {
                    foreach ($categoryIds as $categoryId) {
                            $categoriesIn[] = [
                                'product_id' => $productId,
                                'category_id' => $categoryId,
                                'position' => 0
                            ];
                    }
                }
                if (!empty($categoriesIn)) {
                    $this->resource->getConnection()->insertOnDuplicate($categoryTable, $categoriesIn, ['product_id', 'category_id']);
                }
            }
        }
    }

   /**
    * Delete Previous image
    *
    * @param array $productsArray
    * @return array $productsArray
    */
    private function deletePreviousImages($productsArray)
    {
        foreach ($productsArray as $index => $product) {
           
            if (!empty($product['sku']) && !empty($product['additional_images'])) {
                
                $sku = $product['sku'] ?? '';
                try {
                    $productObject = $this->productRepository->get($sku, false, 0);
                } catch (NoSuchEntityException $e) {
                    $productObject = false;
                }

                if ($productObject instanceof CatalogProduct && !empty($product['additional_images'])) {
                    $remoteImages = $productObject->getMediaGalleryImages();
                    $existingImages = [];
                    foreach ($remoteImages as $child) {
                        $existingImages[]= $child->getFile();
                    }
                
                    $images = !empty($product['additional_images']) ? explode($this->pimMultiValueSeparator, $product['additional_images']) : [];
                    $allMediaAttributevalues = $productObject->getMediaAttributeValues();
                    $commonImages = [];
                    foreach ($images as $key => $imageName) {
                        $tempName = explode('/', urldecode(urldecode($imageName)));
                        $name = end($tempName);
                        $tempName = explode('.', $name);
                        $name = reset($tempName);
                        $matchImage = null;

                        foreach ($existingImages as $checkImage) {
                            if (preg_match('#' . $name . '#i', $checkImage)) {
                                $matchImage = $checkImage;
                                break;
                            }
                        }
                       
                        if ($matchImage) {
                            $commonImages = array_unique(
                                array_merge(
                                    ((array)$matchImage),
                                    $commonImages
                                )
                            );

                            foreach ($allMediaAttributevalues as $imageRole => $roleValue) {
                                if (!empty($productsArray[$index][$imageRole]) && $images[$key] === $productsArray[$index][$imageRole]) {
                                    if ($roleValue == null) {
                                        $productsArray[$index][$imageRole] = $matchImage;
                                    } else {
                                        // unset($productsArray[$index][$imageRole]);
                                    }
                                }
                            }
                            unset($images[$key]);
                        }
                    }

                    $extraImages = array_diff($existingImages, $commonImages);
                    if (!empty($extraImages)) {
                        $flag = false;
                        foreach ($extraImages as $image) {
                            $this->imageProcessor->removeImage($productObject, $image);
                            $flag = true;
                        }
                        if ($flag) {
                            $productObject->save();
                        }
                    }

                    if (!empty($images)) {
                        $productsArray[$index]['additional_images'] = implode($this->pimMultiValueSeparator, $images);
                    }
                }
            }
        }
        return $productsArray;
    }

    private function getEntities($content)
    {
        $data = [];
        if (isset($content['path'])) {
            $csvString = $this->getCurlResponse($content['path']);
            if (isset($csvString['error'])) {
                $data['error'] = $csvString['error'];
                return $data;
            }
            
            if ($csvString) {
                $fp = fopen("php://temp", 'r+');
                fputs($fp, $csvString);
                rewind($fp);
                $csvData = [];
                while ($row = fgetcsv(
                    $fp,
                    0,
                    $content['delimiter'] ?? ',',
                    $content['enclosure'] ??  '"'
                )) {
                    $csvData[] = $row;
                }
                $keys = array_shift($csvData);
                if ($keys && is_array($keys)) {
                    foreach ($keys as $index => $value) {
                        $keys[$index] = preg_replace("/[^\w\d]/", "", $value);
                    }
                }
                foreach ($csvData as $key => $values) {
                    if ($values && !empty($values)) {
                        $data[] = array_combine($keys, $values);
                    }
                }  
            }
        }
        return $data;
    }

    /**
     * Get curl response from csv file
     * @param url of csv file
     */
    private function getCurlResponse($url)
    {
        try {
             $options = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_CONNECTTIMEOUT => 0,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ];
            
            $this->curl->setOptions($options);
            $this->curl->get($url);
            $response = $this->curl->getBody();
        } catch (\Exception $e) {
            $this->logger->critical('Error Curl:- ' . $e->getMessage());
            $response['error'] = $e->getMessage();
        }

        return !empty($response) ? $response : null;
    }

    /** Used To update the catalog_product_relation table after import the products */
    private function updateProductRelationTableUsingQuery()
    {
        $collection = $this->linkFactory->create()->getCollection();
        $productRelationTable = $collection->getTable('catalog_product_relation');
        $productBundleSelectionTable = $collection->getTable('catalog_product_bundle_selection');

        $updateQuery = 'INSERT IGNORE INTO ' . $productRelationTable . ' SELECT parent_product_id,product_id FROM ' . $productBundleSelectionTable;
        $this->resource->getConnection()->query($updateQuery);
    }

   /**
    * Delete options and selections.
    *
    * @param array $productsArray
    *
    * @return void
    */
    public function deleteOptionsAndSelections($productsArray)
    {
        $collection = $this->linkFactory->create()->getCollection();
        foreach ($productsArray as $index => $product) {
            if (!empty($product['sku'])
                && isset($product['store_view_code'])
                && $product['store_view_code'] == ''
            ) {
                if ($product['product_type']=='downloadable') {
                    try {
                        $prodct = $this->productRepository->get($product['sku']);
                        if ($prodct && $prodct->getId()) {
                            $collection = $this->linkFactory->create()->getCollection()->addFieldToFilter('product_id', $prodct->getId());
                            $flag=true;
                            foreach ($collection as $model) {
                                $flag=false;
                                $model->delete();
                            }
                            $collection = $this->sampleFactory->create()->getCollection()->addFieldToFilter('product_id', $prodct->getId());
                            foreach ($collection as $model) {
                                $flag=false;
                                $model->delete();
                            }
                            if ($flag || $prodct->getTypeId()!='downloadable') {
                                $prodct->setTypeId('downloadable');
                                $this->productRepository->save($prodct);
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }
               
                $productId = $this->helper->getProductIdBySku($product['sku']);
                if (!empty($productId)) {
                        $optionTable = $collection->getTable('catalog_product_bundle_option');
                        $optionValueTable = $collection->getTable('catalog_product_bundle_option_value');
                        $selectionTable = $collection->getTable('catalog_product_bundle_selection');
                        $valuesIds =  $this->resource->getConnection()->fetchAssoc($this->resource->getConnection()->select()->from(
                            ['bov' => $optionValueTable],
                            ['value_id']
                        )->joinLeft(
                            ['bo' => $optionTable],
                            'bo.option_id = bov.option_id',
                            ['option_id']
                        )->where(
                            'parent_id IN (?)',
                            $productId
                        ));
                        $this->resource->getConnection()->delete(
                            $optionValueTable,
                            $this->resource->getConnection()->quoteInto('value_id IN (?)', array_keys($valuesIds))
                        );
                        $this->resource->getConnection()->delete(
                            $optionTable,
                            $this->resource->getConnection()->quoteInto('parent_id IN (?)', $productId)
                        );
                        $this->resource->getConnection()->delete(
                            $selectionTable,
                            $this->resource->getConnection()->quoteInto('parent_product_id IN (?)', $productId)
                        );
                }
            }
            continue;
        }
    }

    /**
     * Removes specified selections by specified product id
     *
     * @param array $productsArray
     * @return void
     */
    public function dropAllUnneededSelections($productsArray)
    {
        $collection = $this->sampleFactory->create()->getCollection();
        $skus = array_column($productsArray, 'sku');
        $skus = array_unique($skus);
        $productCollection = $this->helper->getCatBundProCollection($skus);
        if($productCollection->getSize()){
            $productIds = array_column($productCollection->getData(), 'entity_id');
            $where = 'parent_product_id in ('.implode(',', $productIds).')';
            $this->resource->getConnection()->delete($this->collection->getTable('catalog_product_bundle_selection'), $where);
        }
    }

    public function deleteAssociations($productsArray)
    {
        try {
            $collection = $this->sampleFactory->create()->getCollection();
            $skus = array_column($productsArray, 'sku');
            $skus = array_unique($skus);
            $productCollection = $this->helper->getCataProdLinkColl($skus);
            if($productCollection->getSize()){
                $productIds = array_column($productCollection->getData(), 'entity_id');
                $associations = [
                    \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED,
                    \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL,
                    \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL
                ];
                $where = 'product_id in ('.implode(',', $productIds).') and link_type_id in ('.implode(',', $associations).')';
                $this->resource->getConnection()->delete($collection->getTable('catalog_product_link'), $where);
            }
           
        } catch (NoSuchEntityException $e) {

        }
    
    }
}
