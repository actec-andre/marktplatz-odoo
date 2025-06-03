<?php
/**
 * @category   Webkul
 * @package    Webkul_ProductImportQueue
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\ProductImportQueue\Plugin;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\Data\ProductExtensionFactory;

class ProductRepositoryPlugin
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;

    /**
     * @var ProductExtensionFactory
     */
    protected $productExtensionFactory;

    /**
     * @param \Magento\Framework\Webapi\Rest\Request $request
     */
    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        ProductExtensionFactory $productExtensionFactory
    ) {
        $this->request = $request;
        $this->productExtensionFactory = $productExtensionFactory;
    }

    public function beforeSave(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $productData = $this->request->getBodyParams();
        if ($product->getId() &&
            isset($productData['product']['custom_attributes']) && 
            count($productData['product']['custom_attributes']) > 0
        ) {
            foreach ($productData['product']['custom_attributes'] as $attribute) {
                if ($attribute['attribute_code'] == 'url_key_create_redirect') {
                    $product->setData('save_rewrites_history', (bool)$attribute['value']);
                }
            }
        }
    }

}