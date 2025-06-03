# Headless Magento 2 Configuration

## Zielsetzung
Umwandlung des Magento 2.4.7 Shops in eine reine Backend-Lösung für:
- ✅ Admin Dashboard (Produktverwaltung, Bestellungen)
- ✅ API für Akeneo PIM Integration
- ✅ MagnaLista Marktplatz-Integration
- ❌ Frontend/Shop komplett entfernt

## Aktuelle Situation (Stand: 03.06.2025)

### Performance-Verbesserungen erreicht
- **Load Average**: von 23.61 auf 1.12 reduziert (95% Verbesserung)
- **Module deaktiviert**: 20 Frontend-Module erfolgreich deaktiviert
- **Cache-System**: Von Varnish auf Built-in Cache umgestellt
- **Search Engine**: Temporär von Elasticsearch auf MySQL umgestellt

### Module erfolgreich deaktiviert (20 Stück)
```bash
# Phase 1: Frontend-spezifische Module (sofort sicher)
Magento_LayeredNavigation
Magento_Swatches
Magento_SwatchesLayeredNavigation
Magento_ProductVideo
Magento_CatalogWidget
Magento_Newsletter
Magento_Contact
Magento_SendFriend
Magento_Wishlist
Magento_WishlistAnalytics
Magento_Review
Magento_ReviewAnalytics
Magento_ProductAlert
Magento_Persistent
Magento_Cookie
Magento_GoogleAnalytics
Magento_GoogleAdwords
Magento_GoogleGtag
Magento_GoogleOptimizer
Magento_Robots
Magento_Sitemap
Magento_Rss
```

### Probleme behoben
- ✅ Varnish Cache Fehler durch Built-in Cache ersetzt
- ✅ Speicherprobleme durch zusätzlichen 2GB Swap temporär gelöst
- ✅ Elasticsearch Memory Issues durch MySQL Search Engine umgangen

## Verbleibendes Problem: Admin Users Grid

### Symptome
- Admin Users Seite zeigt "6 records found" aber keine Daten
- Indexer Warnungen im Admin Dashboard
- Mögliche Ursachen: Such-Engine Umstellung oder Modul-Abhängigkeiten

### Sofortiger Fix erforderlich
```bash
# 1. Indexer Status prüfen und neu aufbauen
php bin/magento indexer:status
php bin/magento indexer:reindex

# 2. User-spezifische Module prüfen
php bin/magento module:status | grep -i user

# 3. Admin-Grid Cache leeren
php bin/magento cache:clean ui_bookmark
php bin/magento cache:clean block_html
```

## Nächste Optimierungsschritte

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

### Phase 3: Customer Frontend Features
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

## Kritische Module (NIEMALS deaktivieren)

### Core Admin
```bash
Magento_Backend          # Admin Panel
Magento_User            # Admin Users (Problem-Kandidat!)
Magento_Authorization   # Permissions
Magento_Security        # Security
Magento_Ui              # Admin UI Components
```

### API & Integration
```bash
Magento_Webapi          # REST API
Magento_WebapiAsync     # Async API
Magento_WebapiSecurity  # API Security
Magento_Integration     # OAuth Integration
```

### Produktmanagement
```bash
Magento_Catalog         # Produkte
Magento_CatalogInventory # Lagerbestand
Magento_Eav             # Attributsystem
Magento_Indexer         # Indexierung
```

### Akeneo Integration
```bash
Webkul_ProductImportQueue  # Akeneo Connector
```

### MagnaLista Requirements
```bash
Redgecko_Magnalister      # MagnaLista Core
Magento_Sales            # Bestellungen
Magento_Quote            # Warenkorb (Backend)
Magento_Payment          # Zahlungsmethoden
Magento_Shipping         # Versandmethoden
```

## Nach Server-Upgrade (nächste Woche)

### 1. Elasticsearch/OpenSearch wiederherstellen
```bash
# Heap-Speicher auf 1GB setzen
echo "-Xms1g -Xmx1g" >> /etc/elasticsearch/jvm.options

# Such-Engine zurückstellen
php bin/magento config:set catalog/search/engine elasticsearch7

# Index neu aufbauen
php bin/magento indexer:reindex catalogsearch_fulltext
```

### 2. MySQL optimieren
```bash
# my.cnf anpassen
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
```

### 3. Nginx Frontend blockieren
```nginx
# Frontend komplett zu Admin weiterleiten
location / {
    return 301 /admin;
}

# Nur Admin und API erlauben
location /admin { ... }
location /rest { ... }
```

## Monitoring nach Optimierung

### Performance-Metriken
- Load Average < 2.0
- Swap-Nutzung < 50%
- Admin Response Time < 2s
- API Response Time < 1s

### Funktionalitäts-Tests
- [ ] Admin Login funktioniert
- [ ] Produktliste laden
- [ ] Bestellungen anzeigen
- [ ] MagnaLista erreichbar
- [ ] Akeneo API `/rest/V1/process-csv` antwortet
- [ ] User-Grid zeigt Daten

## Rollback-Plan
```bash
# Einzelnes Modul reaktivieren
php bin/magento module:enable [MODULE_NAME]
php bin/magento setup:di:compile
php bin/magento cache:flush

# Komplett-Rollback auf Backup
php bin/magento setup:rollback --code-backup [BACKUP_ID]
```

## Geschätzte Verbesserungen (nach vollständiger Implementierung)

### Performance
- **50-70% weniger Module** geladen
- **30-40% kleinere DI-Compilation**
- **Deutlich weniger Cache-Invalidierung**
- **Admin-Panel 2-3x schneller**

### Speicher
- **300-600MB weniger RAM** pro PHP-Prozess
- **Kleinerer OPcache-Footprint**
- **50% weniger Redis-Cache**

### Sicherheit
- **Keine Frontend-Angriffsfläche**
- **90% weniger öffentliche Endpoints**
- **Keine Customer-Account-Vulnerabilities**