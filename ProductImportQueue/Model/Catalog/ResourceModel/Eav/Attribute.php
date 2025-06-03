<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webkul\ProductImportQueue\Model\Catalog\ResourceModel\Eav;

class Attribute extends \Magento\Catalog\Model\ResourceModel\Eav\Attribute
{

    /**
     * @inheritdoc
     */
    public function getSwatchInputType()
    {
        return self::SWATCH_INPUT_TYPE;
    }


    /**
     * Set attribute type
     *
     * @param string $type
     * @return $this
     */
    public function setSwatchInputType($type)
    {
        return $this->setData(self::SWATCH_INPUT_TYPE, $type);
    }
}
