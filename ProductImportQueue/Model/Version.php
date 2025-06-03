<?php

/**
 * @category   Webkul
 * @package    Webkul_ProductImportQueue
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\ProductImportQueue\Model;

class Version implements \Webkul\ProductImportQueue\Api\VersionInterface
{
    /**
     * const version
     */
    const VERSION = "4.0.2";

    /**
     * {@inheritdoc}
     * */
    public function get()
    {
        return ["VERSION" => self::VERSION];
    }
}
