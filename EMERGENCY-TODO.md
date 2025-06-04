# Magento 2.4.7 Notfall-Wiederherstellung Plan

## 🚨 KRITISCHER STATUS
- **Shop**: HTTP 500 Fehler (komplett down)
- **Admin**: Nicht erreichbar 
- **CLI**: DI-System defekt (`Cannot instantiate interface Magento\Framework\Config\CacheInterface`)
- **Ursache**: OpenSearch Migration hat Dependency Injection zerstört

## Phase 1: Sofortige Stabilisierung (HÖCHSTE PRIORITÄT)

### ✅ 1.1 DI-System Wiederherstellung
- [ ] Alle generated/code, generated/metadata, var/cache, var/di löschen
- [ ] app/etc/config.php: Alle Elasticsearch Module auf ursprüngliche Werte setzen
- [ ] app/etc/di.xml komplett entfernen (falls korrupt)
- [ ] `php bin/magento setup:di:compile` ausführen
- [ ] Cache komplett leeren und neu aufbauen

### ✅ 1.2 Module-Status Rollback
```bash
# In config.php setzen:
'Magento_Elasticsearch' => 1,
'Magento_Elasticsearch7' => 1, 
'Magento_InventoryElasticsearch' => 1,
'Magento_OpenSearch' => 0,
```

### ✅ 1.3 Such-Engine Konfiguration
- [ ] `php bin/magento config:set catalog/search/engine elasticsearch7`
- [ ] OpenSearch komplett deaktivieren
- [ ] Elasticsearch7 als primäre Such-Engine

## Phase 2: Funktionalitätsprüfung

### ✅ 2.1 Basis-Tests
- [ ] `php bin/magento --version` funktioniert
- [ ] `php bin/magento cache:status` zeigt Status an
- [ ] Frontend erreichbar (actec.shop/marktplatz)
- [ ] Admin Backend erreichbar (actec.shop/marktplatz/admin)

### ✅ 2.2 Such-System Tests  
- [ ] `php bin/magento indexer:status` zeigt alle Indexer
- [ ] `php bin/magento indexer:reindex catalogsearch_fulltext` funktioniert
- [ ] Produktsuche im Frontend funktioniert

## Phase 3: Systematische Analyse

### ✅ 3.1 Root-Cause Analyse
- [ ] Warum war ursprünglicher Reindex fehlgeschlagen?
- [ ] Elasticsearch7 vs OpenSearch API-Kompatibilität prüfen
- [ ] Memory/Swap Auslastung während Reindex überwachen

### ✅ 3.2 Alternative Lösungsansätze
- [ ] MySQL-basierte Suche als Fallback testen
- [ ] Elasticsearch Index-Einstellungen optimieren
- [ ] Separate Elasticsearch Instance erwägen

## Phase 4: Langfristige Stabilität

### ✅ 4.1 Performance Optimierung
- [ ] Elasticsearch Heap Size anpassen (aktuell 1.4GB)
- [ ] MySQL Query Cache optimieren 
- [ ] PHP-FPM Memory Limits prüfen

### ✅ 4.2 Monitoring & Alerts
- [ ] Indexer Status Monitoring einrichten
- [ ] Memory Usage Alerts konfigurieren
- [ ] Backup-Strategie für config.php

## Notfall-Befehle (Ready-to-use)

```bash
# DI-System Reset
cd /var/www/html
rm -rf generated/code/* generated/metadata/* var/cache/* var/page_cache/* var/di/*
rm app/etc/di.xml 2>/dev/null

# Module Rollback in config.php
sed -i "s/'Magento_OpenSearch' => 1/'Magento_OpenSearch' => 0/g" app/etc/config.php
sed -i "s/'Magento_Elasticsearch' => 0/'Magento_Elasticsearch' => 1/g" app/etc/config.php  
sed -i "s/'Magento_Elasticsearch7' => 0/'Magento_Elasticsearch7' => 1/g" app/etc/config.php

# DI Neukompilierung
php bin/magento setup:di:compile
php bin/magento cache:clean
php bin/magento cache:flush

# Such-Engine zurücksetzen
php bin/magento config:set catalog/search/engine elasticsearch7
php bin/magento indexer:reindex catalogsearch_fulltext
```

## Aktuelle TODOs (Reihenfolge einhalten!)

1. **[IN PROGRESS]** DI-System Wiederherstellung
2. **[PENDING]** Module-Status Rollback  
3. **[PENDING]** CLI-Funktionalität testen
4. **[PENDING]** Admin Backend Zugang
5. **[PENDING]** Catalog Search Reindexing

---
*Erstellt: 04.06.2025 | Status: NOTFALL | Priorität: KRITISCH*