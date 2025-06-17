# Akeneo PIM System - Analyse (2025-06-17)

## Server-Details (68.183.68.44 - NUR LESERECHTE)
- **IP-Adresse**: 68.183.68.44 (Akeneo Webkul Server)
- **Domain**: https://pim.ananda.gmbh
- **OS**: Ubuntu 22.04.4 LTS (Jammy Jellyfish)
- **Kernel**: Linux 5.15.0-141-generic x86_64
- **RAM**: 7.8GB (6.7GB verwendet, 706MB verfügbar)
- **Festplatte**: 78GB (72GB verwendet, 93% belegt - **Warnung!**)
- **Web-Server**: Apache 2.4 (Docker Container)
- **Uptime**: 13 Minuten (kürzlich neugestartet)
- **Zugriff**: 🔍 **NUR LESERECHTE** - Ausschließlich für Analyse und Dokumentation

## ⚠️ Wichtiger Hinweis
**Dieser Server darf NUR für LESE-Operationen verwendet werden!**
- Keine Änderungen an Konfigurationen
- Keine Installation von Software
- Keine Modifikation von Dateien
- Ausschließlich Analyse zur Dokumentation der Integration

## System-Architektur (Docker-basiert)

### **🐳 Docker-Setup**
- **Container-Count**: 10 aktive Container
- **Orchestrierung**: Docker Compose (`/home/pim-new/docker-compose.yml`)
- **Installation-Pfad**: `/home/pim-new/`
- **Akeneo-Edition**: Community Standard Edition

### **Container-Stack**:
1. **`pim-new_httpd_1`** - Apache 2.4 (Port 8081→80)
2. **`pim-new_fpm_1`** - PHP-FPM 8.1 (Akeneo Core)
3. **`pim-new_php_1`** - PHP 8.1 CLI/Worker
4. **`pim-new_mysql_1`** - MySQL 8.0.30 (Port 33016→3306)
5. **`pim-new_elasticsearch_1`** - Elasticsearch 8.4.2 (Port 9220→9200)
6. **`pim-new_node_1`** - Node.js 18 (Assets/Frontend)
7. **`pim-new_object-storage_1`** - MinIO S3-kompatibel (Ports 9092, 9093)
8. **`pim-new_selenium_1`** - Browser Testing (Port 5920→5900)
9. **`pim-new_blackfire_1`** - Performance Profiling
10. **`pim-new_pubsub-emulator_1`** - Google Cloud PubSub Emulator

### Framework & Backend
- **Framework**: Symfony (erkennbar an Debug Toolbar)
- **PHP-Version**: 8.1 (via akeneo/pim-php-dev:8.1)
- **Login-Route**: `/user/login`
- **API-Basis**: `/api/rest/v1`
- **OAuth-Endpunkt**: `/api/oauth/v1/token`

### Sicherheit & Headers
```http
Content-Security-Policy: default-src 'self' *.akeneo.com 'unsafe-inline'
X-Debug-Token: [verschiedene Tokens erkannt]
Set-Cookie: BAPID=[session-id]; path=/; httponly; samesite=lax
```

## Akeneo API-Endpunkte (Vollständige Liste)

### 🔑 **Authentifizierung**
```bash
POST /api/oauth/v1/token  # OAuth Token-Generierung
```

### 📦 **Produkt-Management**
```bash
# Standard Products
GET    /api/rest/v1/products
POST   /api/rest/v1/products
GET    /api/rest/v1/products/{code}
PATCH  /api/rest/v1/products/{code}
DELETE /api/rest/v1/products/{code}
PATCH  /api/rest/v1/products  # Bulk Update

# UUID-Based Products  
GET    /api/rest/v1/products-uuid
POST   /api/rest/v1/products-uuid
GET    /api/rest/v1/products-uuid/{uuid}
PATCH  /api/rest/v1/products-uuid/{uuid}
DELETE /api/rest/v1/products-uuid/{uuid}
PATCH  /api/rest/v1/products-uuid  # Bulk Update
```

### 🏷️ **Attribute-Management**
```bash
GET   /api/rest/v1/attributes
POST  /api/rest/v1/attributes
GET   /api/rest/v1/attributes/{code}
PATCH /api/rest/v1/attributes/{code}
PATCH /api/rest/v1/attributes  # Bulk Update

# Attribute Options
GET   /api/rest/v1/attributes/{attributeCode}/options
POST  /api/rest/v1/attributes/{attributeCode}/options
GET   /api/rest/v1/attributes/{attributeCode}/options/{code}
PATCH /api/rest/v1/attributes/{attributeCode}/options/{code}
PATCH /api/rest/v1/attributes/{attributeCode}/options  # Bulk
```

### 📁 **Kategorien**
```bash
GET   /api/rest/v1/categories
POST  /api/rest/v1/categories
GET   /api/rest/v1/categories/{code}
PATCH /api/rest/v1/categories/{code}
PATCH /api/rest/v1/categories  # Bulk Update
```

### 🏭 **Familien**
```bash
GET   /api/rest/v1/families
POST  /api/rest/v1/families
GET   /api/rest/v1/families/{code}
PATCH /api/rest/v1/families/{code}
PATCH /api/rest/v1/families  # Bulk Update

# Family Variants
GET   /api/rest/v1/families/{familyCode}/variants
POST  /api/rest/v1/families/{familyCode}/variants
GET   /api/rest/v1/families/{familyCode}/variants/{code}
PATCH /api/rest/v1/families/{familyCode}/variants/{code}
PATCH /api/rest/v1/families/{familyCode}/variants  # Bulk
```

### 🖼️ **Media-Management**
```bash
GET  /api/rest/v1/media-files
POST /api/rest/v1/media-files
GET  /api/rest/v1/media-files/{code}
GET  /api/rest/v1/media-files/{code}/download
```

### 🏢 **DAM (Digital Asset Management)**
```bash
# Assets
GET    /api/rest/v1/dam/asset/{identifier}
GET    /api/rest/v1/asset/dam/list
POST   /api/rest/v1/dam/asset/add
PUT    /api/rest/v1/dam/asset/add/{identifier}
DELETE /api/rest/v1/dam/{code}/delete

# Asset Categories
GET    /api/rest/v1/dam/assetcategory/{code}
GET    /api/rest/v1/dam/assetcategories/list
POST   /api/rest/v1/dam/assetcategory/add
PUT    /api/rest/v1/dam/assetcategory/add/{code}
DELETE /api/rest/v1/dam/assetcategory/{code}/delete

# Asset Families
GET    /api/rest/v1/dam/assetfamily/{code}
GET    /api/rest/v1/dam/assetfamilies/list
POST   /api/rest/v1/dam/assetfamily/add
PUT    /api/rest/v1/dam/assetfamily/add/{code}
DELETE /api/rest/v1/dam/assetfamily/{code}/delete
```

### 🔧 **Webkul Custom APIs**
```bash
# Job Management
POST /api/rest/V1/trigger-import
GET  /api/rest/V1/job-status/{jobInstanceId}

# Table Attributes (Media-Management)
GET    /api/rest/v1/media/attributes/{attributeCode}/column/{code}
GET    /api/rest/v1/media/attributes/{attributeCode}/columns
POST   /api/rest/v1/media/attributes/{attributeId}/columns
PUT    /api/rest/v1/media/attribute-column-edit/{attributeColumnId}
DELETE /api/rest/v1/media/attribute-column-delete/{attributeColumnId}

# Enhanced Group Management
GET    /api/enrich/group/rest/search
GET    /api/enrich/group/rest/{identifier}
GET    /api/enrich/group/rest/{identifier}/products
PATCH  /api/enrich/group/rest/{code}
DELETE /api/enrich/group/rest/{code}
POST   /api/enrich/group/rest

# Configuration Management
DELETE /api/configuration/attribute/{code}
DELETE /api/configuration/attribute-option/{attributeId}/{attributeOptionId}
DELETE /api/configuration/attribute-group/{identifier}
DELETE /api/configuration/rest/family/{code}
DELETE /api/configuration/rest/family-variant/{familyVariantCode}
```

## 🚨 **System-Warnung**
- **Festplatte zu 93% belegt** (72GB/78GB) - Monitoring erforderlich
- **Docker-Container laufen seit 2 Monaten** - potentiell veraltete Images
- **MinIO Object Storage** aktiv für Media-Dateien
- **Elasticsearch** für Produktsuche verfügbar

### 🌍 **Lokalisierung & Kanäle**
```bash
# Locales
GET /api/rest/v1/locales
GET /api/rest/v1/locales/{code}

# Channels
GET   /api/rest/v1/channels
POST  /api/rest/v1/channels
GET   /api/rest/v1/channels/{code}
PATCH /api/rest/v1/channels/{code}
PATCH /api/rest/v1/channels  # Bulk Update

# Currencies
GET /api/rest/v1/currencies
GET /api/rest/v1/currencies/{code}
```

## Wichtige Erkenntnisse für Magento-Integration

### 1. **Media-Export Problem**
- **Problem**: Akeneo ruft `/V1/products/{sku}/media/{entryId}` mit trailing slash auf
- **Magento Standard-API**: Erwartet **ohne** trailing slash
- **Lösung**: Custom Routes in webapi.xml implementiert

### 2. **Authentifizierung**
- **Akeneo verwendet**: OAuth 2.0 mit Bearer Tokens
- **Magento-Integration**: API-Token `9exki8pv300vh3rnjgfqa1gptvzm7uf7`

### 3. **Webkul-Extensions**
- **Akeneo hat Webkul-Extensions** für erweiterte PIM-Funktionen
- **Job-Management**: Automatisierte Import/Export-Prozesse
- **Table Attributes**: Spezielle Attribut-Typen für strukturierte Daten

### 4. **DAM-Integration**
- **Digital Asset Management** vollständig integriert
- **Asset-Families & Categories** für strukturierte Media-Verwaltung
- **Media-Download** über dedizierte API-Endpunkte

## API-Kompatibilität Status

### ✅ **Funktioniert**
- OAuth Token-Authentifizierung
- Standard Produkt-APIs
- Media-Listen (ohne trailing slash)
- Attribute-Management
- Kategorie-Synchronisation

### ⚠️ **Erfordert Fixes**
- Media Entry-Zugriff (trailing slash Problem)
- Bulk-Update-Operationen (Performance)
- Custom Webkul-Endpunkte (Mapping)

### 🔄 **Empfohlene Magento-Seitige Anpassungen**
1. **Media API**: Trailing slash Unterstützung implementiert ✅
2. **Bulk Operations**: Batch-Processing für Performance
3. **Webkul Compatibility**: Spezielle Endpunkte für Webkul-Features
4. **Error Handling**: Robuste Fehlerbehandlung für API-Timeouts

## SSH-Zugriff Setup

**Für Akeneo Server 68.183.68.44 (🔍 NUR LESERECHTE):**
```bash
echo "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIMmoY0U60rp3v3yFbHKJGaiE84uu5Wg1ZTGlSEUl9xY+ minimac" | ssh root@68.183.68.44 "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys"
```

**🔒 Erlaubte Operationen:**
- Datei-Lese-Operationen (`cat`, `ls`, `grep`, `find`)
- Konfiguration-Analyse (`head`, `tail`, `less`)
- Log-Analyse (`journalctl`, `tail -f`)
- Dokumentation und Integration-Verständnis

**❌ Nicht erlaubt:**
- Änderungen an Konfigurationsdateien
- Installation/Deinstallation von Software
- Neustart von Services
- Modifikation von Datenbanken

## Fazit
Das Akeneo-System ist modern aufgebaut mit Symfony, verfügt über umfangreiche REST-APIs und nutzt Webkul-Extensions für erweiterte PIM-Funktionen. Die Hauptherausforderung liegt in der Anpassung der Magento Media-API für trailing slash Kompatibilität.

**Server 68.183.68.44 ist ausschließlich für Analyse-Zwecke mit Leserechten zugänglich.**