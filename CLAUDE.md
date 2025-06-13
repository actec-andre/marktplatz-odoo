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
- **Webkul ProductImportQueue**: ✅ Installiert und aktiviert
- **SSL-Zertifikat**: ✅ Let's Encrypt für actec.shop
- **Upload-Limits**: ✅ 100MB für Akeneo-Dateien konfiguriert

## Backup-Inhalt
- **MagnaLister**: Komplettes Modul + Datenbank-Tabellen
- **Webkul/Akeneo**: ProductImportQueue Modul
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
POST /V1/before-process-csv       # Vor-Validierung
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

## Nächste Schritte
1. ✅ **Magento 2.4.8-p1** Installation abgeschlossen
2. ✅ **Webkul ProductImportQueue** installiert und aktiviert
3. ✅ **SSL-Zertifikat** für actec.shop konfiguriert
4. ✅ **Upload-Limits** auf 100MB für Akeneo erhöht
5. ✅ **API-Endpunkte** getestet und funktionsfähig
6. **MagnaLister Migration** aus dem Backup (ausstehend)
7. **Akeneo-Integration** konfigurieren und testen