<?php
namespace Webkul\ProductImportQueue\Api;

interface BundleOptionManagementInterface
{
    /**
     * Create a new bundle product option
     *
     * @param string $sku
     * @return bool
     */
    public function createOption($sku);

    // /**
    //  * Update an existing bundle product option
    //  *
    //  * @param string $sku
    //  * @param int $optionId
    //  * @return bool
    //  */
    // public function updateOption($sku);
}
