# MagnaLister Migration - PRD & SRS

## 📋 Product Requirements Document (PRD)

### 🎯 Projektübersicht
**Ziel**: Migration des MagnaLister Marketplace-Connectors von altem Server auf neue Hostinger-Installation mit Magento 2.4.8-p1

### 🏢 Business Requirements

#### Marktplatz-Integration
- **eBay**: Produktupload, Bestandsabgleich, Bestellimport
- **Amazon**: Multi-Channel Verkauf mit zentraler Verwaltung  
- **OTTO Market**: B2B Marktplatz-Anbindung
- **Kaufland**: Produktsynchronisation
- **METRO**: Wholesale-Integration
- **Etsy**: Handgemachte Produkte
- **cDiscount**: Französischer Marktplatz

#### Kernfunktionen
1. **Produktmanagement**: Upload einzelner Produkte oder ganzer Kategorien
2. **Bestellverwaltung**: Zentraler Import und Verwaltung von Marktplatz-Bestellungen
3. **Bestandsabgleich**: Automatische Synchronisation zwischen Shop und Marktplätzen
4. **Preismanagement**: Flexible Preisgestaltung je Marktplatz
5. **Versandoptionen**: Konfiguration verschiedener Versandmethoden

### 📊 Stakeholder
- **Primär**: E-Commerce Manager (Verkauf auf Marktplätzen)
- **Sekundär**: Lager-/Bestandsmanagement
- **Technical**: Magento 2 Administrator

### 🎯 Success Criteria
- ✅ Alle Marktplatz-Verbindungen funktionsfähig
- ✅ Historische Konfigurationen wiederhergestellt
- ✅ Nahtlose Bestellabwicklung
- ✅ Kein Datenverlust bei Migration

---

## 🛠️ Software Requirements Specification (SRS)

### 🖥️ Technische Anforderungen

#### Server-Spezifikationen (Hostinger)
- **Server**: root@31.97.47.66 (srv866215.hstgr.cloud)
- **OS**: Ubuntu 24.04.2 LTS
- **PHP**: 8.2.28 (kompatibel mit MagnaLister)
- **MySQL**: Percona Server 8.0.36-28
- **Nginx**: 1.28.0
- **SSL**: Let's Encrypt für actec.shop

#### Magento-Kompatibilität
- **Magento Version**: 2.4.8-p1 ✅
- **Deployment Mode**: `default` (Development-ready)
- **Lokalisierung**: de_DE (Europa/Berlin, EUR)

#### MagnaLister Module-Struktur
```
app/code/Redgecko/Magnalister/
├── registration.php
├── Console/Afterupdate.php
├── Model/ResourceModel/Order/
├── Block/Adminhtml/Order/View/
├── Ui/Component/Listing/Column/
└── [weitere Module-Dateien]
```

#### Datenbank-Tabellen (11 Tabellen)
```sql
magnalister_apirequests      # API-Request Cache
magnalister_config           # Modul-Konfiguration  
magnalister_errorlog         # Fehlerprotokollierung
magnalister_global_selection # Globale Produktauswahl
magnalister_listings_deleted # Gelöschte Listings
magnalister_magnacompat_errorlog # Kompatibilitätsfehler
magnalister_marketplace_status   # Marktplatz-Status
magnalister_orders           # Marktplatz-Bestellungen
magnalister_preparedefaults  # Standard-Vorbereitungen
magnalister_products         # Produktmapping
magnalister_selection        # Produktauswahl
```

### 🔧 Installations-Workflow

#### Composer-Package Details
- **Package**: `redgecko/magnalister` 
- **Version**: 3.0.0 (2024-06-09)
- **Lizenz**: Apache-2.0
- **Repository**: github.com/magnalister/magento2_magnalister
- **Support**: support.woop@magnalister.com

#### Phase 1: Vorbereitung
- [ ] Backup-Verifikation (magnalister_module.tar.gz + magnalister_data.sql)
- [ ] MySQL-Zugangsdaten für Hostinger konfigurieren
- [ ] Magento-Wartungsmodus aktivieren

#### Phase 2: Module-Installation  

**Option A: Composer-Installation (Empfohlen)**
```bash
# 1. Als user wechseln (nicht als root ausführen!)
su - user

# 2. Magento-Verzeichnis
cd /home/user/htdocs/srv866215.hstgr.cloud

# 3. Wartungsmodus aktivieren
php bin/magento maintenance:enable

# 4. MagnaLister via Composer installieren
php -d memory_limit=2G /usr/local/bin/composer require redgecko/magnalister

# 5. Setup-Updates
php bin/magento setup:upgrade
```

**Option B: Backup-Migration (Fallback)**
```bash
# 1. Als user wechseln (nicht als root!)
su - user

# 2. Magento-Verzeichnis
cd /home/user/htdocs/srv866215.hstgr.cloud

# 3. Wartungsmodus aktivieren
php bin/magento maintenance:enable

# 4. MagnaLister Modul entpacken
tar -xzf magnalister_module.tar.gz

# 5. Modul aktivieren
php bin/magento module:enable Redgecko_Magnalister

# 6. Schema-Updates
php bin/magento setup:upgrade
```

#### Phase 3: Datenbank-Migration
```bash
# Import der MagnaLister-Daten
mysql -u [user] -p [database] < magnalister_data.sql
```

#### Phase 4: System-Integration
```bash
# DI-Kompilierung
php bin/magento setup:di:compile

# Static Content Deployment
php bin/magento setup:static-content:deploy de_DE

# Cache-Management
php bin/magento cache:flush

# Wartungsmodus deaktivieren
php bin/magento maintenance:disable
```

### ⚠️ **Wichtige Sicherheitshinweise**
- **NIEMALS als root ausführen**: Alle Composer/Magento-Befehle als `user` ausführen
- **Memory-Limit**: Composer braucht mindestens 2GB RAM für Installation
- **Wartungsmodus**: Während Installation aktivieren für bessere Performance
- **User-Rechte**: `user` (UID: 1002) hat bereits alle nötigen Berechtigungen

### 🔐 Sicherheitsanforderungen
- **TLS 1.2+**: PayPal und Marketplace-APIs
- **API-Credentials**: Sichere Speicherung in Magento-Config
- **Backup-Strategie**: Wöchentliche Snapshots über Hostinger
- **Access Control**: Admin-Only Zugang zu MagnaLister-Konfiguration

### 📈 Performance-Anforderungen
- **Memory**: 512MB PHP memory_limit für größere Produktkataloge
- **Execution Time**: 300s für umfangreiche API-Synchronisationen
- **Database**: Optimierte Indizes für MagnaLister-Tabellen
- **Caching**: OPcache für PHP-Performance

### 🧪 Test-Szenarien

#### Funktionale Tests
1. **Produktupload**: Test-Produkt auf eBay hochladen
2. **Bestellimport**: Dummy-Bestellung von Marktplatz importieren
3. **Bestandsabgleich**: Lagerbestand-Synchronisation testen
4. **API-Verbindung**: Alle Marktplatz-APIs validieren

#### Integration Tests  
1. **Magento-Katalog**: Produktsynchronisation mit MagnaLister
2. **Order-Workflow**: Bestellungen → Magento Sales
3. **Customer-Data**: Kundendaten-Import/-Export
4. **Multi-Store**: Verschiedene Shop-Views unterstützen

### 🚨 Risiko-Assessment
- **Niedrig**: Module-Installation (Standard Magento-Prozess)
- **Mittel**: Datenbank-Import (Backup vorhanden)
- **Niedrig**: API-Rekonfiguration (Dokumentation verfügbar)

### 📝 Deliverables
1. ✅ **Funktionsfähiges MagnaLister-Modul** in Magento 2.4.8-p1
2. ✅ **Migrierte Konfigurationen** aus Backup-Datenbank
3. ✅ **Validierte Marktplatz-Verbindungen** für alle Channels
4. ✅ **Dokumentierte Installation** für zukünftige Updates

---

## 📋 Nächste Schritte
1. **MySQL-Setup** für Hostinger abschließen
2. **MagnaLister-Migration** nach diesem PRD/SRS ausführen
3. **API-Keys konfigurieren** für alle Marktplätze
4. **Go-Live Testing** mit Testprodukten

---
*Erstellt: 13.06.2025 | Status: Bereit für Implementation*