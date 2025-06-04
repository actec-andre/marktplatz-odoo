# Magento 2.4.7 Marktplatz-System Optimierung Plan

## 🎯 Ziel-Definition
**Headless Magento für MagnaLister-Marktplatzanbindung**
- Keine Frontend-Shop-Funktionalität erforderlich
- Fokus: MagnaLister → eBay/Amazon Listings
- Datenfluss: Akeneo PIM → Magento → MagnaLister → Marktplätze
- Order-Verarbeitung: Marktplätze → MagnaLista → odoo.shv16 ERP

## Phase 1: System-Diagnose & Basis-Stabilität ⚡
### 🔍 1.1 Aktueller Status-Check (PRIORITÄT: HOCH)
- [ ] SSH-Verbindung testen (`ssh -i ~/.ssh/id_ed25519 root@165.22.66.230`)
- [ ] Magento CLI-Funktionalität prüfen (`php bin/magento --version`)
- [ ] Admin Backend-Zugang testen (actec.shop/marktplatz/admin)
- [ ] MagnaLister-Modul Status prüfen (`php bin/magento module:status | grep Magnalister`)
- [ ] System-Logs analysieren (system.log, exception.log)

### 🚀 1.2 Performance-Baseline
- [ ] Memory/Swap-Auslastung dokumentieren (`free -h`)
- [ ] MySQL Performance prüfen
- [ ] Redis-Status validieren (`systemctl status redis`)
- [ ] Such-Engine Status (Elasticsearch vs MySQL)

## Phase 2: Headless-Optimierung 🔧
### 🎨 2.1 Frontend-Deaktivierung
- [ ] Alle Frontend-Module deaktivieren (Theme, Layout, etc.)
- [ ] Storefront-Routes deaktivieren
- [ ] CSS/JS-Kompilierung reduzieren
- [ ] Frontend-Cache deaktivieren (nur Backend-Cache behalten)

### ⚙️ 2.2 Admin-Interface Optimierung
- [ ] Nur notwendige Admin-Module aktiv lassen
- [ ] MagnaLister Admin-Integration testen
- [ ] Produkt-Management-Interface prüfen
- [ ] Order-Management-Interface validieren

## Phase 3: MagnaLister-Fokus 📦
### 🛍️ 3.1 MagnaLister Kern-Funktionalität
- [ ] MagnaLister-Konfiguration prüfen
- [ ] eBay-Connector Status testen
- [ ] Amazon-Connector Status testen
- [ ] Listing-Funktionalität validieren
- [ ] Order-Import testen

### 🔄 3.2 Akeneo-Integration
- [ ] Akeneo → Magento Sync-Status prüfen
- [ ] Produkt-Import-Logs analysieren
- [ ] Attribut-Mapping validieren
- [ ] Kategorie-Sync überprüfen

## Phase 4: Performance-Tuning 🚀
### 💾 4.1 Speicher-Optimierung
- [ ] Elasticsearch → MySQL-Suche für Ressourcen-Ersparnis
- [ ] Swap-Reduktion durch Memory-Tuning
- [ ] MySQL-Buffer-Pool optimieren
- [ ] Redis-Cache optimieren

### 🔍 4.2 Index-Management
- [ ] Nur notwendige Indexer aktiv lassen
- [ ] Cron-Jobs für Marktplatz-Umgebung optimieren
- [ ] Background-Prozesse minimieren

## Phase 5: Monitoring & Wartung 📊
### 📈 5.1 Monitoring-Setup
- [ ] MagnaLister-spezifische Logs überwachen
- [ ] Order-Flow Monitoring einrichten
- [ ] Performance-Alerts konfigurieren
- [ ] Backup-Strategie für Marktplatz-Daten

### 🔄 5.2 Wartungs-Automation
- [ ] Automatische Cache-Leerung
- [ ] Log-Rotation optimieren
- [ ] Backup-Schedule für kritische Daten

## Kritische Test-Checkpoints ✅
Nach jeder Phase zu prüfen:

1. **MagnaLister Admin erreichbar**
2. **Produkt-Listings funktional** 
3. **Order-Import läuft**
4. **System-Performance stabil**
5. **Memory/Swap unter Kontrolle**

## Module-Priorities für Headless-Setup
### 🟢 KRITISCH (AKTIV LASSEN)
- Magento_Catalog
- Magento_Sales  
- Magento_Customer
- Magento_Inventory
- Redgecko_Magnalister
- Webkul_ProductImportQueue (für Akeneo)

### 🟡 OPTIONAL (PRÜFEN)
- Magento_ConfigurableProduct
- Magento_Bundle
- Magento_Downloadable

### 🔴 DEAKTIVIEREN (Frontend)
- Magento_Checkout
- Magento_Theme
- Magento_Cms
- Magento_Newsletter
- Magento_Review
- Magento_Wishlist

## Nächste Schritte Empfehlung

**START MIT**: Phase 1.1 - System-Diagnose
**DANN**: Phase 2.1 - Frontend-Deaktivierung  
**FOCUS**: Phase 3 - MagnaLister-Optimierung

---
*Erstellt: 04.06.2025 | Typ: Headless E-Commerce | Fokus: B2B Marktplatz-Integration*