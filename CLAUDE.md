# Claude Code Kontextdokumentation

## Projekt-Übersicht
Frische Magento 2 E-Commerce-Installation auf Hostinger mit MagnaLista und Akeneo/Webkul Integration für Marktplatzanbindungen.

## Claude-Expertise & Fähigkeiten
- **Magento 2 Installation & Administration Expert**: Spezialisiert auf E-Commerce-Plattform-Management, Modul-Installation, Performance-Optimierung
- **MagnaLista & Akeneo Integration**: Expertise in Marktplatz-Synchronisation und PIM-Systemen
- **SSH-Verbindungsmanagement**: Stabile Remote-Administration 
- **Modul-Migration**: Transfer von Magento-Modulen zwischen Servern
- **DI-Kompilierung & Cache-Management**: Dependency Injection und Performance-Tuning

## Server-Zugang (HOSTINGER)
- **Server**: root@31.97.47.66
- **Hostname**: srv866215.hstgr.cloud
- **SSH-Verbindung**: `ssh root@31.97.47.66`
- **OS**: Ubuntu 24.04.2 LTS (Noble Numbat)

## System-Spezifikationen
- **RAM**: 7.8 GB (verfügbar: 2.1 GB)
- **Festplatte**: 96 GB (verfügbar: 87 GB, 10% belegt)
- **Swap**: 2.0 GB
- **PHP**: Version 8.2.28 (downgegradet von 8.4.7)
- **MySQL**: Percona Server 8.0.36-28
- **Nginx**: Version 1.28.0

## Wichtige Pfade
- **Magento Web-Root**: /home/user/htdocs/srv866215.hstgr.cloud/
- **Nginx Document Root**: /home/user/htdocs/srv866215.hstgr.cloud/pub
- **Nginx Konfiguration**: /etc/nginx/sites-enabled/
- **SSL-Zertifikate**: /etc/nginx/ssl-certificates/

## Status
- **Aktueller Status**: ✅ Magento 2.4.8-p1 installiert und funktionsfähig
- **Services**: Nginx, MySQL (Percona), PHP 8.2-FPM aktiv
- **Webkul ProductImportQueue**: ✅ Version 4.0.0 installiert und funktionsfähig
- **SSL-Zertifikat**: ✅ Let's Encrypt für actec.shop
- **Upload-Limits**: ✅ 100MB für Akeneo-Dateien konfiguriert

## Backup-Inhalt
- **MagnaLister**: Komplettes Modul + Datenbank-Tabellen
- **Webkul/Akeneo**: ProductImportQueue Modul
- **Webkul 4.0.0 Archive**: `webkul_4.0.0.tar.gz` (funktionierende Version)
- **Konfigurationen**: app/code/ und app/etc/ Verzeichnisse
- **Datenbank**: Modul-Konfigurationen und MagnaLister-Daten

## Installations-Roadmap
1. **Magento 2 Installation**: Frische Magento 2.4.7+ Installation
2. **Module Migration**: MagnaLister und Webkul Module vom Backup
3. **Datenbank Import**: MagnaLister Konfigurationen und Daten
4. **Testing**: Funktionalitätstests und Performance-Optimierung

## Häufige Befehle (AKTUALISIERT)
```bash
# Server-Verbindung
ssh root@31.97.47.66

# System-Status
ssh root@31.97.47.66 "systemctl status nginx mysql php8.2-fpm"

# Speicher prüfen
ssh root@31.97.47.66 "free -h && df -h"

# Magento-Befehle (korrekter Pfad)
ssh root@31.97.47.66 "cd /home/user/htdocs/srv866215.hstgr.cloud && php bin/magento [command]"
```

## Services
- **Nginx**: Port 80/443, SSL konfiguriert (srv866215.hstgr.cloud)
- **MySQL**: Percona Server 8.0.36-28
- **PHP-FPM**: PHP 8.2.28 mit OPcache

## MySQL-Zugangsdaten (Hostinger)
- **Status**: MySQL-Zugangsdaten werden benötigt
- **Hinweis**: Über Hostinger-Panel neue Zugangsdaten erstellen
- **Für**: Magento-Installation und Akeneo-Integration

## Magento 2 MCP Server

### Installation & Konfiguration
```bash
# Repository bereits geklont unter: ./magento2-mcp
# Konfiguriert mit: claude mcp add magento2
```

### MCP Tools (14 verfügbar)
```bash
# Produkte (9)
get_product_by_sku          # Produkt per SKU
get_product_by_id           # Produkt per ID
search_products             # Einfache Suche
advanced_product_search     # Erweiterte Suche
get_product_categories      # Produkt-Kategorien
get_related_products        # Verwandte Produkte
get_product_stock          # Lagerbestand
get_product_attributes     # Produkt-Attribute
update_product_attribute   # Attribut ändern

# Umsatz/Statistik (4)
get_revenue               # Umsatz (Zeitraum)
get_order_count          # Bestellungen (Zeitraum)
get_product_sales        # Verkaufsstatistiken
get_revenue_by_country   # Umsatz nach Land

# Kunden (1)
get_customer_ordered_products_by_email  # Kundenbestellungen
```

## Admin-Zugang
- **Magento Admin-URL**: https://actec.shop/admin
- **Domain**: actec.shop (SSL-zertifiziert mit Let's Encrypt)
- **Alternative URL**: https://srv866215.hstgr.cloud/admin
- **Base URL**: Konfiguriert für actec.shop

## Akeneo/Webkul API Configuration

### **Upload-Limits (100MB für Akeneo)**
```ini
# PHP 8.2 Settings
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 512M

# Nginx Settings  
client_max_body_size 100M
```

### **Webkul ProductImportQueue API-Endpunkte**
```bash
# Basis-URL: https://actec.shop/rest

# Haupt-Endpunkte für Akeneo
POST /V1/process-csv              # CSV-Import von Akeneo
POST /V1/wkproducts              # Produkt-Import/Create
PUT  /V1/wkproducts/{sku}        # Produkt-Update

# Attribute-Management
GET  /V1/products/wk_attributes   # Verfügbare Attribute
GET  /V1/products/wk_attributesList # Attribute-Sets
POST /V1/products/attributes/{code}/options # Attribut-Optionen

# Kategorie-Management  
POST /V1/categories              # Kategorie erstellen
PUT  /V1/categories/{id}         # Kategorie aktualisieren

# Validierung
GET  /V1/validate/version        # Webkul-Version prüfen
```

### **Integration Setup**
1. **System → Integrations** im Magento Admin
2. **Add New Integration**: "Akeneo API"
3. **Alle Rechte** vergeben für vollständigen Zugang
4. **API-Tokens** für Akeneo-Konfiguration verwenden

### **WICHTIG: OAuth Bearer Token für Magento 2.4.4+**
```bash
# KRITISCHER FIX für Akeneo-Integration in Magento 2.4.4+
ssh root@31.97.47.66 "cd /home/user/htdocs/srv866215.hstgr.cloud && php bin/magento config:set oauth/consumer/enable_integration_as_bearer 1"

# Anschließend Cache leeren
ssh root@31.97.47.66 "cd /home/user/htdocs/srv866215.hstgr.cloud && php bin/magento cache:flush"
```

**Hintergrund**: Magento 2.4.4+ benötigt diese Konfiguration für Bearer Token-Authentication über REST API. Ohne diesen Befehl funktioniert Akeneo-Integration nicht (HTTP 500 Fehler).

## Webkul ProductImportQueue Migration (2025-01-16)

### **Problem**: Akeneo-Inkompatibilität
- **Ursprüngliche Version**: 4.0.2 (hatte zusätzlichen `/V1/before-process-csv` Endpunkt)
- **Funktionierende Version**: 4.0.0 (Server 167.172.170.235)
- **Lösung**: Downgrade von 4.0.2 auf 4.0.0

### **Migration durchgeführt**:
```bash
# 1. Wartungsmodus + Backup
ssh root@31.97.47.66 "cd /home/user/htdocs/srv866215.hstgr.cloud && php bin/magento maintenance:enable"
cp -r ./app/code/Webkul/ProductImportQueue /tmp/webkul_backup/

# 2. Version 4.0.0 Installation
rm -rf ./app/code/Webkul/ProductImportQueue
# Kopiert von funktionierendem Server 167.172.170.235

# 3. Abhängigkeit installiert
composer require firegento/fastsimpleimport

# 4. Magento Update
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
php bin/magento maintenance:disable
```

### **Verifikation erfolgreich**:
- ✅ **Version**: 4.0.0 aktiv
- ✅ **API-Endpunkte**: Alle funktionieren
- ✅ **Problematischer Endpunkt entfernt**: `/V1/before-process-csv` → 404
- ✅ **Akeneo-kompatibel**: Gleiche API-Struktur wie funktionierender Server

## Akeneo PIM Server (68.183.68.44) - WARTUNG & TROUBLESHOOTING

### **KRITISCHER FIX: Job Queue Consumer (2025-06-17)**
**Problem**: Export-Jobs hängen dauerhaft im Status "STARTING 0/2" - Jobs werden nie verarbeitet
**Ursache**: Fehlender Job Queue Consumer Service in docker-compose.yml
**Lösung**: Permanenter Job Consumer Service hinzugefügt

```yaml
# Hinzugefügter Service in docker-compose.yml
job-consumer:
  image: 'akeneo/pim-php-dev:8.1'
  restart: unless-stopped
  environment:
    APP_ENV: '${APP_ENV:-prod}'
  volumes:
    - './:/srv/pim'
  working_dir: '/srv/pim'
  command: 'bin/console messenger:consume ui_job import_export_job data_maintenance_job --env=prod -vv'
  depends_on:
    - mysql
    - elasticsearch
  networks:
    - 'pim'
```

**Status**: ✅ Job Consumer läuft automatisch, verarbeitet hängende Jobs sofort
**Backup**: docker-compose.yml.backup vorhanden

### **Server-Zugang (NUR LESEN + SLEEP)**
```bash
# WICHTIG: Immer 'sleep 3' vor SSH-Befehlen verwenden (Rate Limiting)
sleep 3; ssh root@68.183.68.44
cd /home/pim-new

# Docker Status prüfen
docker-compose ps
```

### **Webkul Support: Storage-Bereinigung (2025-06-17)**
**Problem**: Akeneo läuft voll wegen alter Export-Archive und Job-Executions

**Offizielle Webkul-Lösung:**
```bash
# 1. Alte Export-Archive löschen (17GB freigemacht!)
rm -rf /home/pim-new/var/file_storage/archive/export/csv_product_export/*

# 2. Alte Job-Executions bereinigen (458 Jobs gelöscht!)
cd /home/pim-new
docker-compose exec -T fpm bin/console akeneo:batch:purge-job-execution --days=30 --env=prod
```

**Ergebnis**: Speicher von 93% auf 36% reduziert, System läuft wieder stabil.

### **Sync-Jobs Troubleshooting**
```bash
# Alle hängenden Export-Jobs stoppen
cd /home/pim-new
docker-compose exec -T fpm bin/console messenger:stop-workers --env=prod

# Hängende Job-Logs löschen (falls nötig)
rm -rf var/logs/batch/[job-id]

# Container-Neustart für komplette Bereinigung
docker-compose restart fmp

# Cache leeren nach Problemen
docker-compose exec -T fpm bin/console cache:clear --env=prod
```

### **Job-Status prüfen**
```bash
# Aktive Batch-Jobs finden
find var/logs/batch/ -name '*.log' -mmin -30

# Laufende Prozesse prüfen
docker-compose exec -T fpm ps aux | grep -E '(export|batch|sync)'

# Verfügbare Export-Jobs anzeigen
docker-compose exec -T fpm bin/console akeneo:batch:list-jobs --env=prod | grep export
```

### **Performance-Monitoring**
```bash
# Speicher-Status prüfen
df -h
du -sh var/file_storage/archive/export/

# Docker Container Ressourcen
docker-compose exec -T fpm top -bn1
```

**WICHTIG**: Server 68.183.68.44 und 167.172.170.235 haben NUR LESE-RECHTE!

## KRITISCHES PROBLEM IDENTIFIZIERT: Fehlende DAM-Module (2025-06-17)

### **ROOT CAUSE: Download-Modul fehlt in Magento**
**Problem**: Akeneo-Jobs laufen erfolgreich, aber Datasheet-Attribute werden nicht in Magento importiert
**Ursache**: Funktionierender Shop verwendet `experius/module-wysiwygdownloads` für Download-Funktionalität

### **Akeneo DAM Bundle (aktiv)**:
- `src/Webkul/DamBundle/Controller/MediaController.php` - Datasheet Downloads
- `src/Webkul/DamBundle/Normalizer/ExternalApi/ProductNormalizer.php` - Export-Logik
- Behandelt Media/Asset-Attribute und Downloads

### **Solarcraft.isotoxin.com (funktionierend)**:
- `experius/module-wysiwygdownloads: 1.2.3` - **WYSIWYG Download-Funktionalität**
- Ermöglicht PDF/DOC/Excel-Downloads auf Produktseiten
- Downloads-Sektion mit Englisch/Deutsch Datenblättern

### **Actec.shop (fehlend)**:
- `app/code/Webkul/ProductImportQueue/` - NUR Basis-Import
- **FEHLT**: `experius/module-wysiwygdownloads` Modul
- Keine Download-Funktionalität verfügbar

### **LÖSUNG: Experius Downloads Modul installieren**:
```bash
composer require experius/module-wysiwygdownloads
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### **Funktionierender Shop: solarcraft.isotoxin.com (167.172.170.235)**:
- **SSH-Zugang**: `ssh root@167.172.170.235`
- **Magento-Pfad**: `/mnt/volume_fra1_01/www/solarcraft.isotoxin.com/`
- **Webkul Module**: `app/code/Webkul/ProductImportQueue/`
- **Bestätigt**: Downloads-Sektion funktioniert (Englisch/Deutsch Datenblätter)
- **Nur Downloadable.php gefunden**: Vollständige DAM-Analyse erforderlich

## Nächste Schritte
1. ✅ **Magento 2.4.8-p1** Installation abgeschlossen
2. ✅ **Webkul ProductImportQueue 4.0.0** Migration erfolgreich
3. ✅ **SSL-Zertifikat** für actec.shop konfiguriert
4. ✅ **Upload-Limits** auf 100MB für Akeneo erhöht
5. ✅ **API-Endpunkte** getestet und Akeneo-kompatibel
6. ✅ **OAuth Bearer Token** für Magento 2.4.4+ konfiguriert (KRITISCHER FIX)
7. ✅ **Akeneo Storage-Bereinigung** durchgeführt (17GB freigemacht)
8. ✅ **Problem identifiziert**: Fehlende DAM-Module in Magento
9. **Webkul DAM Bundle für Magento** installieren (KRITISCH)
10. **MagnaLister Migration** aus dem Backup (ausstehend)