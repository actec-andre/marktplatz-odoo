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

interface VersionInterface
{
    /**
     * Return the version of Module
     *
     * @api
     * @param void
     * @return void
     */
    public function get();
}
