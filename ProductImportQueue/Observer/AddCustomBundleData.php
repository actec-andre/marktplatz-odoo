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
 use Magento\Framework\Event\Observer;
 use Magento\Catalog\Api\Data\ProductExtensionFactory;
 use Magento\Bundle\Model\Product\Type as BundleProductType;
 use Magento\Catalog\Api\ProductRepositoryInterface;
 
 class AddCustomBundleData implements ObserverInterface
 {
     protected $productExtensionFactory;
     protected $productRepository;
 
     public function __construct(
         ProductExtensionFactory $productExtensionFactory,
         ProductRepositoryInterface $productRepository
     ) {
         $this->productExtensionFactory = $productExtensionFactory;
         $this->productRepository = $productRepository;
     }
 
     public function execute(Observer $observer)
     {
         $product = $observer->getEvent()->getProduct();
 
         if ($product->getTypeId() === BundleProductType::TYPE_CODE) {
            // Get or create extension attributes
            $extensionAttributes = $product->getExtensionAttributes() ?? $this->productExtensionFactory->create();
        
            // Get bundle product options
            $bundleOptions = $extensionAttributes->getBundleProductOptions();
            $customBundleOptions = [];
        
            if (!empty($bundleOptions)) {
                foreach ($bundleOptions as $option) {
                    $optionData = $option->getData();
                    
                    // Map product links if they exist
                    if (isset($optionData['product_links'])) {
                        $optionData['product_links'] = array_map(function ($link) {
                            return $link->getData();
                        }, $optionData['product_links']);
                    }
        
                    $customBundleOptions[] = $optionData;
                }
            }
            // Set custom bundle options and data
            $extensionAttributes->setBundleCustomData($customBundleOptions);
            
            // Apply modified extension attributes to the product
            $product->setExtensionAttributes($extensionAttributes);
        }
    }
 }
 