<?php

/**
 * @category   Webkul
 * @package    Webkul_ProductImportQueue
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\ProductImportQueue\Model\Import\Product\Type;

class Downloadable extends \Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable
{

    /**
     * Validate row attributes. Pass VALID row data ONLY as argument.
     *
     * @param array $rowData
     * @param int $rowNum
     * @param bool $isNewProduct Optional
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        $this->rowNum = $rowNum;
        $error = false;
        if (!$this->isRowDownloadableNoValid($rowData)) {
            $this->_entityModel->addRowError(self::ERROR_OPTIONS_NOT_FOUND, $this->rowNum);
            $error = true;
        }
        
        if ($this->downloadableHelper->isRowDownloadableEmptyOptions($rowData)) {
            // $this->_entityModel->addRowError(self::ERROR_COLS_IS_EMPTY, $this->rowNum);
            // $error = true;
        }

        if ($this->isRowValidSample($rowData) || $this->isRowValidLink($rowData)) {
            $error = true;
        }
        
        return !$error;
    }

    /**
     * Check whether the row is valid.
     *
     * @param array $rowData
     * @return bool
     */
    public function isRowDownloadableNoValid(array $rowData)
    {
        if (isset($rowData['_store'])
            && ($rowData['_store'] != 'all' || $rowData['_store'] != '')
        ) {
            return true;
        }

        $result = isset($rowData[self::COL_DOWNLOADABLE_SAMPLES]) ||
            isset($rowData[self::COL_DOWNLOADABLE_LINKS]);

        return $result;
    }
}
