# Magento 2 Module Analysis for Headless Setup

## Project Requirements
- Admin Dashboard: REQUIRED
- API for Akeneo PIM Integration: REQUIRED  
- MagnaLista marketplace integration: REQUIRED
- Product management: REQUIRED
- Order management: REQUIRED
- No customer-facing frontend: NOT NEEDED

## SAFE TO DISABLE - Frontend-Only Modules (Currently Enabled)

### 1. Customer Experience Modules
```bash
# Newsletter functionality
Magento_Newsletter
Magento_NewsletterGraphQl

# Product reviews
Magento_Review
Magento_ReviewAnalytics
Magento_ReviewGraphQl

# Wishlist functionality  
Magento_Wishlist
Magento_WishlistAnalytics
Magento_WishlistGraphQl

# Send to friend
Magento_SendFriend
Magento_SendFriendGraphQl

# Contact forms
Magento_Contact
Magento_ContactGraphQl

# Product comparison
Magento_CompareListGraphQl
```

### 2. Frontend UI Features  
```bash
# Layered navigation for filters
Magento_LayeredNavigation

# Product swatches (color/size)
Magento_Swatches
Magento_SwatchesGraphQl
Magento_SwatchesLayeredNavigation

# Cookie notice/management
Magento_Cookie

# Persistent shopping cart
Magento_Persistent
```

### 3. Analytics & Tracking Modules
```bash
# Frontend analytics
Magento_Analytics
Magento_CustomerAnalytics
Magento_QuoteAnalytics
Magento_SalesAnalytics
Magento_NewRelicReporting
```

### 4. ReCaptcha Frontend Modules
```bash
# All frontend form captchas
Magento_ReCaptchaCheckout
Magento_ReCaptchaCheckoutSalesRule
Magento_ReCaptchaContact
Magento_ReCaptchaCustomer
Magento_ReCaptchaFrontendUi
Magento_ReCaptchaNewsletter
Magento_ReCaptchaReview
Magento_ReCaptchaSendFriend
Magento_ReCaptchaStorePickup
Magento_ReCaptchaWishlist
```

### 5. GraphQL Modules (If Not Using GraphQL API)
```bash
# Base GraphQL
Magento_GraphQl
Magento_GraphQlCache
Magento_GraphQlNewRelic
Magento_GraphQlResolverCache
Magento_GraphQlServer
Magento_AdminGraphQlServer

# All GraphQL endpoints
Magento_EavGraphQl
Magento_StoreGraphQl
Magento_DirectoryGraphQl
Magento_CatalogGraphQl
Magento_CatalogCustomerGraphQl
Magento_CatalogCmsGraphQl
Magento_CatalogUrlRewriteGraphQl
Magento_CatalogRuleGraphQl
Magento_CatalogInventoryGraphQl
Magento_BundleGraphQl
Magento_ConfigurableProductGraphQl
Magento_GroupedProductGraphQl
Magento_DownloadableGraphQl
Magento_CustomerDownloadableGraphQl
Magento_CustomerGraphQl
Magento_CheckoutAgreementsGraphQl
Magento_CmsGraphQl
Magento_CmsUrlRewriteGraphQl
Magento_ContactGraphQl
Magento_GiftMessageGraphQl
Magento_IntegrationGraphQl
Magento_InventoryGraphQl
Magento_InventoryQuoteGraphQl
Magento_InventoryInStorePickupGraphQl
Magento_InventoryInStorePickupQuoteGraphQl
Magento_LoginAsCustomerGraphQl
Magento_NewsletterGraphQl
Magento_OrderCancellationGraphQl
Magento_PaymentGraphQl
Magento_PaymentServicesPaypalGraphQl
Magento_PaypalGraphQl
Magento_QuoteGraphQl
Magento_ReCaptchaWebapiGraphQl
Magento_RelatedProductGraphQl
Magento_ReviewGraphQl
Magento_SalesGraphQl
Magento_SalesRuleGraphQl
Magento_SendFriendGraphQl
Magento_ServicesIdGraphQlServer
Magento_SwatchesGraphQl
Magento_TaxGraphQl
Magento_UrlRewriteGraphQl
Magento_VaultGraphQl
Magento_WeeeGraphQl
Magento_WishlistGraphQl
Magento_CompareListGraphQl
```

### 6. Login As Customer Feature
```bash
# Complete suite of LoginAsCustomer modules
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
```

### 7. Other Frontend Modules
```bash
# Multiple shipping addresses
Magento_Multishipping

# One-click purchase
Magento_InstantPurchase

# Price/stock alerts
Magento_ProductAlert

# XML sitemap generation
Magento_Sitemap

# RSS feeds
Magento_Rss

# Robots.txt
Magento_Robots

# Catalog widgets for CMS
Magento_CatalogWidget

# PayPal captcha
Magento_PaypalCaptcha
```

### 8. In-Store Pickup Frontend
```bash
Magento_InventoryInStorePickupQuote
Magento_InventoryInStorePickupSales
Magento_InventoryInStorePickupMultishipping
Magento_InventoryInStorePickupQuoteGraphQl
Magento_InventoryInStorePickupGraphQl
```

### 9. Inventory Frontend Modules
```bash
Magento_InventoryWishlist
```

## KEEP - Required for Admin/API/Core

### Core System
- `Magento_Store` - Core store functionality
- `Magento_Config` - Configuration system
- `Magento_Backend` - Admin backend
- `Magento_User` - Admin users
- `Magento_Authorization` - Access control
- `Magento_Security` - Security features
- `Magento_Integration` - API integrations

### Catalog & Products
- `Magento_Catalog` - Product catalog
- `Magento_CatalogInventory` - Inventory management
- `Magento_CatalogRule` - Catalog price rules
- `Magento_CatalogSearch` - Search functionality
- `Magento_CatalogImportExport` - Import/export
- `Magento_Eav` - Entity-Attribute-Value
- `Magento_Indexer` - Indexing system

### Sales & Orders
- `Magento_Sales` - Order management
- `Magento_Quote` - Quote system
- `Magento_SalesSequence` - Order numbering
- `Magento_SalesRule` - Cart price rules
- `Magento_Checkout` - Checkout process (API)
- `Magento_Payment` - Payment methods
- `Magento_Shipping` - Shipping methods

### Customer (For Orders)
- `Magento_Customer` - Customer management
- `Magento_CustomerImportExport` - Customer import/export

### Admin Features
- `Magento_AdminNotification` - Admin notifications
- `Magento_Backup` - Database backup
- `Magento_Reports` - Admin reports
- `Magento_ImportExport` - Import/export functionality

### API & Integration
- `Magento_Webapi` - REST API
- `Magento_WebapiAsync` - Async API
- `Magento_WebapiSecurity` - API security
- `Redgecko_Magnalister` - MagnaLista integration
- `Webkul_ProductImportQueue` - Product import queue

### Search (Currently MySQL)
- `Magento_Search` - Base search functionality
- `Magento_AdvancedSearch` - Advanced search
- `Magento_Elasticsearch` - Elasticsearch base (keep for future)
- `Magento_Elasticsearch7` - Elasticsearch 7 (keep for future)

### Media & Content
- `Magento_MediaStorage` - Media storage
- `Magento_Cms` - CMS pages (for admin)

### Required Infrastructure
- `Magento_Cron` - Cron jobs
- `Magento_MessageQueue` - Message queue
- `Magento_AsynchronousOperations` - Async operations
- `Magento_Deploy` - Deployment

## UNCERTAIN - Need Analysis

### Inventory Management
Most inventory modules should be kept for proper stock management, but some frontend-specific ones could potentially be disabled:
- `Magento_InventoryWishlist` - Wishlist inventory reservation

### Data Export
- `Magento_DataExporter` - Check if needed for integrations
- `Magento_SalesDataExporter` - Check if needed for integrations
- `Magento_StoreDataExporter` - Check if needed for integrations

### Adobe Services
- `Magento_AdobeIms` - Adobe IMS integration
- `Magento_AdobeStockAdminUi` - Adobe Stock in admin
- Various Adobe Stock modules - Check if using Adobe Stock

### URL Management
- `Magento_UrlRewrite` - URL rewrites (might be needed for API)
- `Magento_CatalogUrlRewrite` - Catalog URL rewrites

### Other
- `Magento_ServicesConnector` - Services connector
- `Magento_ServicesId` - Services ID
- `Magento_SaaSCommon` - SaaS common

## Disable Commands

To disable the safe modules, run these commands on the server:

```bash
# Connect to server
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230

# Navigate to Magento root
cd /var/www/html

# Disable frontend modules (run in batches to avoid issues)
php bin/magento module:disable \
Magento_Newsletter \
Magento_NewsletterGraphQl \
Magento_Review \
Magento_ReviewAnalytics \
Magento_ReviewGraphQl \
Magento_Wishlist \
Magento_WishlistAnalytics \
Magento_WishlistGraphQl \
Magento_SendFriend \
Magento_SendFriendGraphQl

php bin/magento module:disable \
Magento_Contact \
Magento_ContactGraphQl \
Magento_CompareListGraphQl \
Magento_LayeredNavigation \
Magento_Swatches \
Magento_SwatchesGraphQl \
Magento_SwatchesLayeredNavigation \
Magento_Cookie \
Magento_Persistent

php bin/magento module:disable \
Magento_Analytics \
Magento_CustomerAnalytics \
Magento_QuoteAnalytics \
Magento_SalesAnalytics \
Magento_NewRelicReporting

# Disable ReCaptcha modules
php bin/magento module:disable \
Magento_ReCaptchaCheckout \
Magento_ReCaptchaCheckoutSalesRule \
Magento_ReCaptchaContact \
Magento_ReCaptchaCustomer \
Magento_ReCaptchaFrontendUi \
Magento_ReCaptchaNewsletter \
Magento_ReCaptchaReview \
Magento_ReCaptchaSendFriend \
Magento_ReCaptchaStorePickup \
Magento_ReCaptchaWishlist

# Disable LoginAsCustomer modules
php bin/magento module:disable \
Magento_LoginAsCustomer \
Magento_LoginAsCustomerAdminUi \
Magento_LoginAsCustomerApi \
Magento_LoginAsCustomerAssistance \
Magento_LoginAsCustomerFrontendUi \
Magento_LoginAsCustomerGraphQl \
Magento_LoginAsCustomerLog \
Magento_LoginAsCustomerPageCache \
Magento_LoginAsCustomerQuote \
Magento_LoginAsCustomerSales

# Disable other frontend modules
php bin/magento module:disable \
Magento_Multishipping \
Magento_InstantPurchase \
Magento_ProductAlert \
Magento_Sitemap \
Magento_Rss \
Magento_Robots \
Magento_CatalogWidget \
Magento_PaypalCaptcha

# Disable In-Store Pickup frontend modules
php bin/magento module:disable \
Magento_InventoryInStorePickupQuote \
Magento_InventoryInStorePickupSales \
Magento_InventoryInStorePickupMultishipping \
Magento_InventoryInStorePickupQuoteGraphQl \
Magento_InventoryInStorePickupGraphQl

# After disabling, run:
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
php bin/magento cache:flush
```

## Important Notes

1. **GraphQL Modules**: Only disable if you're certain you're not using GraphQL API. If using REST API only, these are safe to disable.

2. **Analytics Modules**: Safe to disable if you don't need frontend analytics. Admin reports will still work.

3. **ReCaptcha Modules**: Keep `Magento_ReCaptchaAdminUi`, `Magento_ReCaptchaUser`, and base modules for admin security.

4. **Test in Staging**: Always test module disabling in a staging environment first.

5. **Backup**: Create a full backup before disabling modules.

6. **Dependencies**: Some modules may have dependencies. Magento will warn if you try to disable a required module.