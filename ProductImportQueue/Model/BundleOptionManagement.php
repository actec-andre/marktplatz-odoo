<?php
namespace Webkul\ProductImportQueue\Model;

use Webkul\ProductImportQueue\Api\BundleOptionManagementInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

class BundleOptionManagement implements BundleOptionManagementInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var array
     */
    protected $_titleArray = [];

    /**
     * @var array
     */
    protected $allStoreIds = [];

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepositoryInterface;

    /**
     * Constructor
     *
     * @param ResourceConnection $resource,
     * @param \Magento\Framework\Webapi\Rest\Request $request,
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepositoryInterface
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepositoryInterface
    ) {
        $this->resource = $resource;
        $this->request = $request;
        $this->storeRepositoryInterface = $storeRepositoryInterface;
    }

    // /**
    //  * Create a bundle option for a given SKU.
    //  *
    //  * @param string $sku
    //  * @return bool
    //  * @throws LocalizedException
    //  */
    // public function createOption($sku)
    // {
    //     return $this->updateOption($sku);
    // }

    /**
     * Update a bundle option for a given SKU.
     *
     * @param string $sku
     * @return bool
     * @throws LocalizedException
     */
    public function createOption($sku)
    {
        $optionDataArray = $this->getJsonData();
        $connection = $this->resource->getConnection();
        $transaction = $connection->beginTransaction();
        $return = [];
        try {
            foreach ($optionDataArray as $optionData) {
                $productLinksData = $optionData['product_links'] ?? [];
                unset($optionData['product_links']);

                // Update bundle option
                $optionSql = $this->generateOptionSQL($optionData);
                list($optionSql, $optionData) = $optionSql;
                $connection->query($optionSql, $optionData);
                $returnOptionData = $optionData;

                // Update product links
                $linkData = [];
                foreach ($productLinksData as $productLink) {
                    $productLinkSql = $this->generateProductLinksSQL($productLink);
                    list($productLinkSql, $productLinkData) = $productLinkSql;
                    $connection->query($productLinkSql, $productLinkData);
                    $linkData[] = $productLinkData;
                }

                // Merge product links data with option data
                $returnOptionData['product_links'] = $linkData;
                $return[] = $returnOptionData;
            }
            $transaction->commit();
            if (count($this->_titleArray)) {
                $this->insertOnDuplicate(
                    'catalog_product_bundle_option_value',
                    $this->_titleArray,
                    [
                        'title'
                    ]
                );
            }
            return $return;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Generate SQL for inserting/updating a bundle option.
     *
     * @param array $optionData
     * @return string
     */
    protected function generateOptionSQL($optionData)
    {
        $storeIds = $this->allStoreIds();
        foreach ($storeIds as $storeId) {
            $this->_titleArray[] = [
                'title' => $optionData['title'],
                'option_id' => $optionData['option_id'],
                'store_id' => $storeId,
                'parent_product_id' => $optionData['parent_id']
            ];
        }
        unset($optionData['title']);
        $sql = "INSERT INTO catalog_product_bundle_option (
            parent_id, required, position, type, zindex, caracteristica1_titulo, 
            caracteristica2_titulo, caracteristica3_titulo, caracteristica4_titulo, show_custom_title, 
            pavilion_view, pavilion_sentence, pavilion_min_options_choice, pavilion_max_options_choice, option_id
        ) VALUES (
            :parent_id, :required, :position, :type, :zindex, 
            :caracteristica1_titulo, :caracteristica2_titulo, :caracteristica3_titulo, :caracteristica4_titulo, 
            :show_custom_title, :pavilion_view, :pavilion_sentence, :pavilion_min_options_choice, :pavilion_max_options_choice, :option_id
        ) ON DUPLICATE KEY UPDATE ";
        
        $existingRecord = $this->resource->getConnection()->fetchRow(
            "SELECT * FROM catalog_product_bundle_option
            WHERE option_id = :option_id",
            [
                ':option_id' => $optionData['option_id']
            ]
        );
        
        $mergedData = array_merge($existingRecord, $optionData);
        
        $updateColumns = [];
        $boundValues = [':option_id' => $optionData['option_id']];
        
        foreach ($mergedData as $column => $value) {
            if ($column != 'option_id') {
                $updateColumns[] = "$column = :$column";
                $boundValues[":$column"] = $value;
            }
        }
        
        $sql .= implode(", ", $updateColumns) . ";";
        
        return [$sql, $boundValues];
    }
    /**
     * Generate SQL for inserting/updating bundle product links.
     *
     * @param array $productLink
     * @return string
     */
    protected function generateProductLinksSQL($productLink)
    {
        $sql = "SELECT COUNT(*) AS count
                FROM catalog_product_bundle_selection
                WHERE option_id = :option_id
                AND parent_product_id = :parent_product_id
                AND product_id = :product_id";
        
        $boundValues = [
            ':option_id' => $productLink['option_id'],
            ':parent_product_id' => $productLink['parent_product_id'],
            ':product_id' => $productLink['product_id'],
        ];
        
        $result = (int)$this->resource->getConnection()->fetchOne($sql, $boundValues);
        if ($result > 0) {
            // Fetch existing record's data
            $existingRecord = $this->resource->getConnection()->fetchRow(
                "SELECT * FROM catalog_product_bundle_selection
                WHERE option_id = :option_id
                AND parent_product_id = :parent_product_id
                AND product_id = :product_id",
                $boundValues
            );
            
            // Merge new data with existing data
            $mergedData = array_merge($existingRecord, $productLink);
            
            $sql = "UPDATE catalog_product_bundle_selection
                    SET ";
            
            $boundValues = [
                ':option_id' => $productLink['option_id'],
                ':parent_product_id' => $productLink['parent_product_id'],
                ':product_id' => $productLink['product_id'],
            ];
            
            $updateColumns = [];
            foreach ($mergedData as $column => $value) {
                $updateColumns[] = "$column = :$column";
            }
            
            $boundValues = array_merge($boundValues, array_combine(
                array_map(function($column) { return ":$column"; }, array_keys($mergedData)),
                array_values($mergedData)
            ));
            
            $sql .= implode(", ", $updateColumns) . "
                    WHERE option_id = :option_id
                    AND parent_product_id = :parent_product_id
                    AND product_id = :product_id";
        } else {
            $sql = "INSERT INTO catalog_product_bundle_selection (
                option_id, parent_product_id, product_id, position, is_default,
                selection_price_type, selection_price_value, selection_qty
            ) VALUES (
                :option_id, :parent_product_id, :product_id, :position, :is_default,
                :selection_price_type, :selection_price_value, :selection_qty
            )";
            
            $boundValues = [
                ':option_id' => $productLink['option_id'],
                ':parent_product_id' => $productLink['parent_product_id'],
                ':product_id' => $productLink['product_id'],
                ':position' => $productLink['position'] ?? 0,
                ':is_default' => $productLink['is_default'] ?? 0,
                ':selection_price_type' => $productLink['selection_price_type'] ?? 0,
                ':selection_price_value' => $productLink['selection_price_value'] ?? 0,
                ':selection_qty' => $productLink['selection_qty'] ?? 0,
            ];
        }
        
        return [$sql, $boundValues];
    }
        

    /**
     * Get JSON data from the request body
     */
    public function getJsonData()
    {
        // Get the raw JSON body from the request
        $jsonBody = $this->request->getContent();
        return json_decode($jsonBody, true);
    }
    /**
     * Insert multiple
     *
     * @param string $tableName
     * @param array $data
     * @param array $fields
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Exception
     */
    public function insertOnDuplicate($tableName, $data, $fields = [])
    {
        try {
            $tableName = $this->resource->getTableName($tableName);
            return $this->resource->getConnection()->insertOnDuplicate($tableName, $data, $fields);
        } catch (\Exception $e) {
            if ($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY
                && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
            ) {
                throw new \Magento\Framework\Exception\AlreadyExistsException(
                    __('URL key for specified store already exists.')
                );
            }
            throw $e;
        }
    }
    /**
     * All Store Ids
     *
     * @return array
     */
    public function allStoreIds()
    {
        if (count($this->allStoreIds)) {
            return $this->allStoreIds;
        }
        $stores =  $this->storeRepositoryInterface->getList();
        foreach ($stores as $store) {
            $this->allStoreIds[] = $store->getId();
        }
        return $this->allStoreIds;
    }
}
