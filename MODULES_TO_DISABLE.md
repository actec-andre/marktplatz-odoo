# Magento 2 Modules to Disable Analysis

## Goal
Keep only modules required for:
- Admin Dashboard functionality
- MagnaLista marketplace integration
- Product management
- Order management
- API functionality

## Modules That Can Be Safely Disabled

### 1. Frontend/Theme Related Modules
```bash
# Core frontend modules
Magento_Theme
Magento_Cookie
Magento_PageCache
Magento_LayeredNavigation
Magento_Swagger
Magento_SwaggerWebapi
Magento_SwaggerWebapiAsync

# Page Builder frontend
Magento_PageBuilder
Magento_PageBuilderAnalytics
Magento_CatalogPageBuilderAnalytics
Magento_CmsPageBuilderAnalytics
Magento_PageBuilderAdminAnalytics
```

### 2. Customer Account Related Modules
```bash
# Customer frontend functionality
Magento_CustomerDownloadableGraphQl
Magento_CustomerGraphQl
Magento_LoginAsCustomer
Magento_LoginAsCustomerAdminUi
Magento_LoginAsCustomerApi
Magento_LoginAsCustomerAssistance
Magento_LoginAsCustomerFrontendUi
Magento_LoginAsCustomerGraphQl
Magento_LoginAsCustomerLog
Magento_LoginAsCustomerPageCache
Magento_LoginAsCustomerQuote
Magento_LoginAsCustomerSales
Magento_Persistent
Magento_Wishlist
Magento_WishlistAnalytics
Magento_WishlistGraphQl
Magento_CompareListGraphQl
```

### 3. Checkout/Cart Related Modules
```bash
# Checkout and cart frontend
Magento_Checkout
Magento_CheckoutAgreements
Magento_CheckoutAgreementsGraphQl
Magento_Multishipping
Magento_InstantPurchase
Magento_AdvancedCheckout
Magento_InventoryAdvancedCheckout
Magento_InventoryInStorePickupMultishipping
Magento_QuoteBundleOptions
Magento_QuoteConfigurableOptions
Magento_QuoteDownloadableLinks
```

### 4. CMS/Content Blocks Modules
```bash
# CMS frontend functionality
Magento_CmsGraphQl
Magento_CmsUrlRewrite
Magento_CmsUrlRewriteGraphQl
Magento_Contact
Magento_ContactGraphQl
Magento_Sitemap
```

### 5. Search/Catalog Frontend Modules
```bash
# Search and catalog frontend
Magento_CatalogSearch
Magento_AdvancedSearch
Magento_Elasticsearch
Magento_Elasticsearch7
Magento_InventoryElasticsearch
Magento_CatalogGraphQl
Magento_CatalogCustomerGraphQl
Magento_CatalogCmsGraphQl
Magento_CatalogUrlRewriteGraphQl
Magento_InventoryCatalogFrontendUi
Magento_InventoryCatalogSearch
Magento_InventoryCatalogSearchBundleProduct
Magento_InventoryCatalogSearchConfigurableProduct
```

### 6. Payment Gateways (Keep only OfflinePayments for MagnaLista)
```bash
# Payment modules to disable
Magento_Paypal
Magento_PaypalGraphQl
Magento_PaypalCaptcha
PayPal_Braintree
PayPal_BraintreeCustomerBalance
PayPal_BraintreeGiftCardAccount
PayPal_BraintreeGiftWrapping
PayPal_BraintreeGraphQl
Magento_CardinalCommerce
Magento_PaymentGraphQl
Magento_PaymentServicesDashboard
Magento_PaymentServicesBase
Magento_PaymentServicesPaypal
Magento_PaymentServicesPaypalGraphQl
Magento_PaymentServicesSaaSExport
# Keep: Magento_OfflinePayments (for bank transfer, cash on delivery)
```

### 7. Shipping Methods (Keep only OfflineShipping for MagnaLista)
```bash
# Shipping modules to disable
Magento_Dhl
Magento_Fedex
Magento_Ups
Magento_Usps
Magento_InventoryInStorePickup
Magento_InventoryInStorePickupAdminUi
Magento_InventoryInStorePickupApi
Magento_InventoryInStorePickupFrontend
Magento_InventoryInStorePickupGraphQl
Magento_InventoryInStorePickupQuote
Magento_InventoryInStorePickupQuoteGraphQl
Magento_InventoryInStorePickupSales
Magento_InventoryInStorePickupSalesApi
Magento_InventoryInStorePickupSalesAdminUi
Magento_InventoryInStorePickupShipping
Magento_InventoryInStorePickupShippingAdminUi
Magento_InventoryInStorePickupShippingApi
Magento_InventoryInStorePickupWebapiExtension
Magento_ReCaptchaStorePickup
# Keep: Magento_OfflineShipping (for flat rate, table rates)
```

### 8. Email Templates for Customers
```bash
# Email and newsletter modules
Magento_Email
Magento_Newsletter
Magento_NewsletterGraphQl
Magento_SendFriend
Magento_SendFriendGraphQl
Magento_ReCaptchaNewsletter
Magento_ReCaptchaSendFriend
```

### 9. Additional Frontend-Only Modules
```bash
# Reviews and ratings
Magento_Review
Magento_ReviewAnalytics
Magento_ReviewGraphQl
Magento_ReCaptchaReview

# Product alerts
Magento_ProductAlert
Magento_InventoryProductAlert

# Gift functionality
Magento_GiftMessage
Magento_GiftMessageGraphQl

# Downloadable products frontend
Magento_DownloadableGraphQl

# Bundle products frontend
Magento_BundleGraphQl

# Configurable products frontend
Magento_ConfigurableProductGraphQl

# Grouped products frontend
Magento_GroupedProductGraphQl

# Related products
Magento_RelatedProductGraphQl

# Swatches frontend
Magento_SwatchesGraphQl
Magento_SwatchesLayeredNavigation
Magento_InventorySwatchesFrontendUi

# RSS feeds
Magento_Rss

# Google tracking
Magento_GoogleAdwords
Magento_GoogleAnalytics
Magento_GoogleGtag
Magento_GoogleOptimizer

# Product video
Magento_ProductVideo

# Widget functionality
Magento_CatalogWidget

# ReCaptcha frontend modules
Magento_ReCaptchaCheckout
Magento_ReCaptchaCheckoutSalesRule
Magento_ReCaptchaContact
Magento_ReCaptchaCustomer
Magento_ReCaptchaFrontendUi
Magento_ReCaptchaWishlist

# Analytics
Magento_Analytics
Magento_AdminAnalytics
Magento_CatalogAnalytics
Magento_CustomerAnalytics
Magento_QuoteAnalytics
Magento_SalesAnalytics

# Sample data
Magento_SampleData

# Robots.txt
Magento_Robots

# Weee (tax) frontend
Magento_WeeeGraphQl

# Tax frontend
Magento_TaxGraphQl

# Sales frontend
Magento_InventorySalesFrontendUi

# Inventory frontend
Magento_InventoryConfigurableProductFrontendUi
Magento_InventoryWishlist

# Store frontend GraphQL
Magento_StoreGraphQl
Magento_DirectoryGraphQl
Magento_ThemeGraphQl

# Quote GraphQL
Magento_QuoteGraphQl
Magento_InventoryQuoteGraphQl

# Sales GraphQL
Magento_SalesGraphQl
Magento_SalesRuleGraphQl

# Vault GraphQL
Magento_VaultGraphQl

# Currency frontend
Magento_CurrencySymbol

# URL rewrites frontend
Magento_UrlRewriteGraphQl

# EAV GraphQL
Magento_EavGraphQl

# Order cancellation UI
Magento_OrderCancellationGraphQl
Magento_OrderCancellationUi
```

## Modules That MUST Stay Enabled

### Core System Modules
- Magento_Store
- Magento_Config
- Magento_Directory
- Magento_Backend
- Magento_User
- Magento_Authorization
- Magento_Security
- Magento_Integration
- Magento_Webapi
- Magento_WebapiAsync
- Magento_WebapiSecurity

### Product Management
- Magento_Catalog
- Magento_CatalogInventory
- Magento_CatalogImportExport
- Magento_CatalogRule
- Magento_CatalogUrlRewrite
- Magento_Eav
- Magento_Indexer
- Magento_ImportExport
- Magento_AdvancedPricingImportExport

### Order Management
- Magento_Sales
- Magento_SalesSequence
- Magento_SalesRule
- Magento_SalesInventory
- Magento_Quote
- Magento_Payment
- Magento_OfflinePayments
- Magento_Shipping
- Magento_OfflineShipping

### Inventory Management
- Magento_Inventory*Api modules
- Magento_Inventory*AdminUi modules
- Core inventory modules (without frontend/GraphQL)

### Required for MagnaLista
- Redgecko_Magnalister
- Magento_Tax
- Magento_TaxImportExport
- Magento_Msrp
- Magento_ConfigurableProduct
- Magento_Bundle
- Magento_GroupedProduct
- Magento_Downloadable
- Magento_Vault

### Media and Content Management
- Magento_MediaStorage
- Magento_MediaGallery
- Magento_MediaGalleryApi
- Magento_MediaGalleryUi
- Magento_MediaGalleryUiApi
- Magento_Cms (keep for admin content management)

### System Infrastructure
- Magento_Cron
- Magento_MessageQueue
- Magento_AsynchronousOperations
- Magento_ApplicationPerformanceMonitor
- Magento_Deploy
- Magento_Variable
- Magento_Translation
- Magento_RequireJs
- Magento_Ui

## Disable Command Template

```bash
# SSH to server and disable modules in batches
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "cd /var/www/html && php bin/magento module:disable \
MODULE_NAME_1 \
MODULE_NAME_2 \
MODULE_NAME_3"

# After disabling, run setup upgrade and clear cache
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "cd /var/www/html && php bin/magento setup:upgrade && php bin/magento cache:clean && php bin/magento cache:flush"
```

## Important Notes

1. **Test in staging first**: Always test module disabling in a staging environment
2. **Backup before changes**: Create a full backup before disabling modules
3. **Disable in batches**: Disable modules in small batches to identify issues
4. **Monitor logs**: Check system.log and exception.log after each batch
5. **MagnaLista dependencies**: Some modules might be required by MagnaLista even if not obvious

## Recommended Approach

1. Start with obvious frontend modules (themes, customer account, checkout)
2. Test MagnaLista functionality after each batch
3. Keep payment and shipping modules that MagnaLista might use for order import
4. Monitor admin functionality and API endpoints
5. Document any modules that cause issues when disabled