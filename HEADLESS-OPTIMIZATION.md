# Magento Headless Optimierung

## Ziel
Magento 2 Shop als reines Backend optimieren:
- ✅ Admin Dashboard behalten
- ✅ API für Akeneo behalten  
- ✅ MagnaLista Integration behalten
- ❌ Frontend/Shop entfernen

## Module zum Deaktivieren

### Frontend/Theme Module
```bash
# Diese Module sind für Frontend-Darstellung:
Magento_Theme
Magento_Swatches
Magento_SwatchesLayeredNavigation
Magento_Ui (teilweise - prüfen!)
Magento_Captcha
Magento_GoogleReCaptcha
```

### Customer/Account Module
```bash
# Kundenkonto-Funktionen:
Magento_Customer
Magento_CustomerAnalytics
Magento_CustomerImportExport
Magento_Persistent
Magento_LoginAsCustomer
Magento_Reward (falls installiert)
```

### Checkout/Cart Module
```bash
# Warenkorb/Checkout-Prozess:
Magento_Checkout
Magento_CheckoutAgreements
Magento_Quote
Magento_QuoteAnalytics
Magento_QuoteGraphQl
Magento_InstantPurchase
Magento_Multishipping
```

### CMS/Content Module
```bash
# Content Management:
Magento_Cms
Magento_CmsGraphQl
Magento_CmsUrlRewrite
Magento_CmsUrlRewriteGraphQl
Magento_Widget
Magento_Newsletter
Magento_Contact
```

### Search/Navigation Module
```bash
# Frontend-Suche:
Magento_Search
Magento_CatalogSearch
Magento_LayeredNavigation
Magento_AdvancedSearch
```

### Payment Module (selektiv)
```bash
# Zahlungsarten (außer was MagnaLista braucht):
Magento_AuthorizenetAcceptjs
Magento_AuthorizenetCardinal
Magento_AuthorizenetGraphQl
Magento_Braintree
Magento_BraintreeGraphQl
Magento_Paypal
Magento_PaypalCaptcha
Magento_PaypalGraphQl
```

### Shipping Module (selektiv)
```bash
# Versandarten (außer was MagnaLista braucht):
Magento_Fedex
Magento_Dhl
Magento_Ups
Magento_Usps
```

### Analytics/Reporting (optional)
```bash
# Analytics (können weg wenn nicht gebraucht):
Magento_Analytics
Magento_GoogleAnalytics
Magento_GoogleGtag
Magento_GoogleOptimizer
Magento_NewRelicReporting
```

## Module BEHALTEN (kritisch!)

### Core Admin
```bash
Magento_Backend
Magento_AdminNotification
Magento_User
Magento_Authorization
Magento_Security
```

### Produkt/Katalog
```bash
Magento_Catalog
Magento_CatalogInventory
Magento_CatalogRule
Magento_CatalogUrlRewrite
Magento_ConfigurableProduct
Magento_GroupedProduct
Magento_BundleProduct
```

### API/Integration
```bash
Magento_Webapi
Magento_WebapiAsync
Magento_WebapiSecurity
Magento_Integration
Magento_GraphQl (falls gebraucht)
```

### Bestellungen
```bash
Magento_Sales
Magento_SalesRule
Magento_SalesSequence
```

### MagnaLista Requirements
```bash
# Diese müssen geprüft werden:
Redgecko_Magnalister
Webkul_ProductImportQueue
# Abhängigkeiten von MagnaLista
```

## Analyseergebnisse (03.06.2025)

### Aktueller Status
- **Enabled Module**: 320+ Module aktiv
- **Disabled Module**: 3 (TwoFactorAuth, OpenSearch, AdminAdobeImsTwoFactorAuth)
- **Disk Usage**: Magento Core 464MB, Custom Code 30MB
- **Themes**: Luma + Blank Themes vorhanden

## Sichere Deaktivierung - Phasenplan

### Phase 1: Frontend-spezifische Module (sofort sicher)
```bash
php bin/magento module:disable \
    Magento_LayeredNavigation \
    Magento_Swatches \
    Magento_SwatchesLayeredNavigation \
    Magento_ProductVideo \
    Magento_CatalogWidget \
    Magento_Newsletter \
    Magento_Contact \
    Magento_SendFriend \
    Magento_Wishlist \
    Magento_WishlistAnalytics \
    Magento_Review \
    Magento_ReviewAnalytics \
    Magento_ProductAlert \
    Magento_Persistent \
    Magento_Cookie \
    Magento_GoogleAnalytics \
    Magento_GoogleAdwords \
    Magento_GoogleGtag \
    Magento_GoogleOptimizer \
    Magento_Robots \
    Magento_Sitemap \
    Magento_Rss
```

### Phase 2: Theme und Widget Module
```bash
php bin/magento module:disable \
    Magento_Theme \
    Magento_ThemeGraphQl \
    Magento_Widget \
    Magento_Cms \
    Magento_CmsGraphQl \
    Magento_CmsPageBuilderAnalytics \
    Magento_CmsUrlRewrite \
    Magento_CmsUrlRewriteGraphQl \
    Magento_PageBuilder \
    Magento_PageBuilderAnalytics \
    Magento_PageBuilderAdminAnalytics
```

### Phase 3: Kundenkonto Frontend Features
```bash
php bin/magento module:disable \
    Magento_LoginAsCustomerFrontendUi \
    Magento_LoginAsCustomerPageCache \
    Magento_InventoryCatalogFrontendUi \
    Magento_InventoryConfigurableProductFrontendUi \
    Magento_InventorySwatchesFrontendUi \
    Magento_InventoryInStorePickupFrontend \
    Magento_InventorySalesFrontendUi \
    Magento_Captcha \
    Magento_ReCaptchaFrontendUi \
    Magento_ReCaptchaCheckout \
    Magento_ReCaptchaContact \
    Magento_ReCaptchaCustomer \
    Magento_ReCaptchaNewsletter \
    Magento_ReCaptchaReview \
    Magento_ReCaptchaSendFriend \
    Magento_ReCaptchaWishlist
```

### Phase 4: Erweiterte Frontend Features (mit Vorsicht!)
```bash
php bin/magento module:disable \
    Magento_Multishipping \
    Magento_InstantPurchase \
    Magento_GiftMessage \
    Magento_GiftMessageGraphQl \
    Magento_Downloadable \
    Magento_DownloadableGraphQl \
    Magento_Bundle \
    Magento_BundleGraphQl \
    Magento_GroupedProduct \
    Magento_GroupedProductGraphQl \
    Magento_AdvancedSearch \
    Magento_CatalogSearch
```

## Implementation pro Phase

### Nach jeder Phase ausführen:
```bash
# 1. Backup erstellen
php bin/magento maintenance:enable
php bin/magento setup:backup --code

# 2. Module kompilieren
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean && php bin/magento cache:flush

# 3. Testen (siehe Checkliste unten)
php bin/magento maintenance:disable
```

### 3. Nginx Frontend blockieren
```nginx
# Alle Frontend-Routes zu Admin weiterleiten
location / {
    return 301 /admin;
}

# Nur Admin und API zulassen
location /admin { ... }
location /rest { ... }
location /graphql { ... }
```

### 4. Themes entfernen
```bash
# Standard-Themes löschen:
rm -rf pub/static/frontend/
rm -rf var/view_preprocessed/pub/static/frontend/
```

### 5. Cron-Jobs optimieren
```bash
# Nur notwendige Crons behalten:
- indexer:reindex
- Akeneo sync
- MagnaLista sync
- Sales/Order cleanup
```

## Erwartete Vorteile

### Performance
- 30-50% weniger PHP-Module geladen
- Kleinere DI-Compilation
- Weniger Cache-Invalidierung
- Schnellere Admin-Aufrufe

### Speicher
- Weniger RAM-Verbrauch pro Request
- Kleinere OPcache
- Weniger Redis-Cache benötigt

### Sicherheit
- Kleinere Angriffsfläche
- Keine Customer-Login-Risiken
- Weniger öffentliche Endpoints

## Testing-Protokoll

### Nach jeder Phase prüfen:
- [ ] **Admin Dashboard**: `/admin` lädt ohne Fehler
- [ ] **Produktliste**: Catalog → Products funktioniert
- [ ] **Bestellungen**: Sales → Orders zeigt Daten
- [ ] **MagnaLista**: Redgecko → MagnaLista erreichbar
- [ ] **Akeneo API**: `curl https://165.22.66.230/rest/V1/process-csv` antwortet
- [ ] **System Logs**: `tail -f var/log/system.log` zeigt keine kritischen Fehler
- [ ] **Exception Logs**: `tail -f var/log/exception.log` leer
- [ ] **Memory Usage**: `free -h` - weniger RAM-Verbrauch
- [ ] **Load Average**: `uptime` - niedrigere Last

### Rollback bei Problemen:
```bash
# Einzelnes Modul wieder aktivieren:
php bin/magento module:enable [MODULE_NAME]
php bin/magento setup:di:compile
php bin/magento cache:flush

# Kompletter Rollback:
php bin/magento setup:rollback --code-backup [BACKUP_ID]
```

## Erwartete Verbesserungen

### Performance-Gains:
- **30-50% weniger Module** geladen pro Request
- **15-25% kleinere DI-Compilation**
- **Deutlich weniger Cache-Invalidierung**
- **Schnellere Admin-Panel Responses**

### Memory-Einsparungen:
- **Geschätzt 200-500MB weniger RAM** pro PHP-Prozess
- **Kleinerer OPcache-Footprint**
- **Reduzierte Redis-Cache-Nutzung**

### Sicherheits-Vorteile:
- **Keine Frontend-Angriffsfläche**
- **Weniger öffentliche Endpoints**
- **Keine Customer-Login-Schwachstellen**

## Rollback-Plan
```bash
# Falls Probleme auftreten:
php bin/magento module:enable [MODULE_NAME]
php bin/magento setup:di:compile
php bin/magento cache:flush
```