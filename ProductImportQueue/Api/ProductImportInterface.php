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

interface ProductImportInterface
{
    /**
     * process csv
     *
     * @api
     * @param mixed $data
     * @return void
     */
    public function processCsv(
        $data
    );

    /**
     * before process csv
     *
     * @api
     * @param mixed $data
     * @return void
     */
    public function beforeProcessCsv(
        $data
    );
}
