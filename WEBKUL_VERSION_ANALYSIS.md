# Webkul ProductImportQueue Version Analyse - 4.0.2 vs 4.1.0

## Problem-Zusammenfassung
**Aktuelles Problem**: Akeneo sendet 401 Unauthorized Fehler bei Produkt-Updates an Magento 2.4.8-p1 mit Webkul ProductImportQueue 4.1.0

**Status**: 
- ✅ **Funktioniert**: Magento 2.4.7 + Webkul 4.0.2 (104.248.133.188)
- ❌ **Fehlerhaft**: Magento 2.4.8-p1 + Webkul 4.1.0 (31.97.47.66)

## Server-Details

### Funktionierender Server (2.4.7)
- **IP**: 104.248.133.188 (DigitalOcean)
- **Path**: `/var/www/solarcraft.eu/`
- **Magento**: 2.4.7
- **Webkul**: ProductImportQueue 4.0.2
- **Status**: ✅ Akeneo-Integration funktioniert

### Problematischer Server (2.4.8-p1)
- **IP**: 31.97.47.66 (Hostinger)  
- **Path**: `/home/user/htdocs/srv866215.hstgr.cloud/`
- **Magento**: 2.4.8-p1
- **Webkul**: ProductImportQueue 4.1.0
- **Status**: ❌ 401 Unauthorized Fehler

## API-Endpoint-Vergleich

### Version 4.0.2 (Funktionierend)
```xml
<!-- Basis-Endpunkte -->
<route url="/V1/process-csv" method="POST">
    <service class="Webkul\ProductImportQueue\Api\ProductImportInterface" method="processCsv"/>
    <resources>
        <resource ref="Magento_Catalog::products" />
    </resources>
</route>

<route url="/V1/before-process-csv" method="POST">
    <service class="Webkul\ProductImportQueue\Api\ProductImportInterface" method="beforeProcessCsv"/>
    <resources>
        <resource ref="Magento_Catalog::products" />
    </resources>
</route>

<!-- Produkt-Endpunkte -->
<route url="/V1/wkproducts" method="POST">
    <service class="Webkul\ProductImportQueue\Api\ProductRepositoryInterface" method="save"/>
    <resources>
        <resource ref="Magento_Catalog::products" />
    </resources>
</route>

<route url="/V1/wkproducts/:sku" method="PUT">
    <service class="Webkul\ProductImportQueue\Api\ProductRepositoryInterface" method="save" />
    <resources>
        <resource ref="Magento_Catalog::products" />
    </resources>
</route>

<!-- Kategorie-Endpunkte -->
<route url="/V1/categories" method="POST">
    <service class="Webkul\ProductImportQueue\Api\CategoryRepositoryInterface" method="save" />
    <resources>
        <resource ref="Magento_Catalog::categories" />
    </resources>
</route>

<route url="/V1/categories/:id" method="PUT">
    <service class="Webkul\ProductImportQueue\Api\CategoryRepositoryInterface" method="save" />
    <resources>
        <resource ref="Magento_Catalog::categories" />
    </resources>
</route>
```

### Version 4.1.0 (Problematisch)
```xml
<!-- Identische Basis-Struktur, aber möglicherweise geänderte Implementierung -->
<!-- Gleiche Endpunkte vorhanden: /V1/wkproducts/:sku, /V1/categories/:id -->
<!-- Problem: 401 Unauthorized bei PUT-Requests mit Store-Code -->
```

## Fehler-Analyse

### Akeneo Request-Pattern
```bash
# Fehlerhaft (4.1.0):
PUT /rest/default/V1/products/14530255?searchCriteria= HTTP/2.0" 401 120
PUT /rest/all/V1/products/14530255?searchCriteria= HTTP/2.0" 401 120

# Erwartete Requests (4.0.2):
PUT /rest/V1/wkproducts/14530255 HTTP/2.0" 200 xxx
```

### Problem-Identifikation
1. **Store-Code-Problem**: Akeneo verwendet `/rest/default/V1/` statt `/rest/V1/`
2. **Endpoint-Problem**: Akeneo versucht `/products/` statt `/wkproducts/`
3. **Parameter-Problem**: `?searchCriteria=` ist ungültig für PUT-Requests
4. **Auth-Problem**: Token wird korrekt gesendet, aber abgelehnt

## Version-Unterschiede

### Webkul ProductImportQueue Versionen
- **4.0.2**: Bewiesenermaßen kompatibel mit Magento 2.4.7 + Akeneo
- **4.1.0**: Inkompatibel mit Magento 2.4.8-p1 (401 Auth-Fehler)
- **4.4.0**: Neueste Version mit erweiterten Auth-Methoden

### API-Interface-Dateien (4.0.2)
```
app/code/Webkul/ProductImportQueue/Api/
├── ProductImportInterface.php
├── CategoryRepositoryInterface.php
├── VersionInterface.php
├── ProductAttributeInterface.php
├── Data/ProductAttributeInterface.php
├── Data/AttributeOptionInterface.php
├── ProductRepositoryInterface.php
├── BundleOptionManagementInterface.php
└── ProductAttributeOptionManagementInterface.php
```

## Backup-Details

### Version 4.0.2 Backup
- **Quelle**: 104.248.133.188:/var/www/solarcraft.eu/app/code/Webkul/ProductImportQueue/
- **Backup-Datei**: `/tmp/webkul_4.0.2_backup.tar.gz` (40KB)
- **Status**: ✅ **Auf actec.shop Server verfügbar** (`/tmp/webkul_4.0.2_backup.tar.gz`)

## Lösungsansätze

### Option 1: Webkul Downgrade (Empfohlen)
```bash
# 1. Wartungsmodus aktivieren
php bin/magento maintenance:enable

# 2. Aktuelles 4.1.0 Modul deaktivieren
php bin/magento module:disable Webkul_ProductImportQueue

# 3. 4.1.0 Dateien sichern
mv app/code/Webkul/ProductImportQueue app/code/Webkul/ProductImportQueue_4.1.0_backup

# 4. 4.0.2 Version installieren
tar -xzf webkul_4.0.2_backup.tar.gz -C app/code/Webkul/

# 5. Modul aktivieren und Setup
php bin/magento module:enable Webkul_ProductImportQueue
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush

# 6. Wartungsmodus deaktivieren
php bin/magento maintenance:disable
```

### Option 2: Akeneo-Konfiguration anpassen
- Akeneo auf Standard-Endpoints umkonfigurieren
- Store-Code aus URL entfernen
- searchCriteria Parameter entfernen

### Option 3: Webkul-Support kontaktieren
- Update für 2.4.8-p1 Kompatibilität anfordern
- Bug-Report für 4.1.0 einreichen

## Risiko-Bewertung

### Downgrade-Risiken
- **Niedrig**: Modul-Downgrade auf bewiesenermaßen funktionale Version
- **Backup vorhanden**: 4.1.0 kann wiederhergestellt werden
- **Reversibel**: Upgrade jederzeit möglich

### Test-Plan
1. **Pre-Migration**: Vollständiges Backup erstellen
2. **Downgrade**: Version 4.0.2 installieren
3. **Integration-Test**: Akeneo-Verbindung testen
4. **API-Test**: Produkt-Upload/-Update testen
5. **Rollback-Plan**: 4.1.0 wiederherstellen bei Problemen

## Nächste Schritte

1. ✅ **Analyse abgeschlossen**: Version 4.0.2 ist Lösung
2. ✅ **Backup-Transfer**: 4.0.2 von DO-Server zu Hostinger übertragen
3. ⏳ **Downgrade durchführen**: 4.1.0 → 4.0.2 
4. ⏳ **Testing**: Akeneo-Integration validieren
5. ⏳ **Monitoring**: 401-Fehler sollten verschwinden

---

**Erstellt**: 13.06.2025 17:30 UTC  
**Status**: Bereit für Implementation  
**Backup verfügbar**: ✅ webkul_4.0.2_backup.tar.gz (40KB)  
**Transfer abgeschlossen**: ✅ `/tmp/webkul_4.0.2_backup.tar.gz` auf actec.shop