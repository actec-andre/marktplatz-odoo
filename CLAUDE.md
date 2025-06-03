# Claude Code Kontextdokumentation

## Projekt-Übersicht
Dieses Projekt verwaltet eine Magento 2.4.7 E-Commerce-Installation mit MagnaLista-Integration für Marktplatzanbindungen.

## Server-Zugang
- **Server**: root@165.22.66.230
- **SSH-Key (MacBook Air)**: ~/.ssh/id_andre_sh
- **SSH-Key (Mac Mini)**: ~/.ssh/id_ed25519
- **Verbindung MacBook Air**: `ssh -i ~/.ssh/id_andre_sh root@165.22.66.230`
- **Verbindung Mac Mini**: `ssh -i ~/.ssh/id_ed25519 root@165.22.66.230`

## Wichtige Pfade
- **Magento-Root**: /var/www/html
- **MagnaLista-Modul**: /var/www/html/app/code/Redgecko/Magnalister/
- **Magento-CLI**: /var/www/html/bin/magento

## Häufige Befehle

### Magento-Status prüfen
```bash
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "cd /var/www/html && php bin/magento --version"
```

### Cache leeren
```bash
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "cd /var/www/html && php bin/magento cache:clean && php bin/magento cache:flush"
```

### Module auflisten
```bash
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "cd /var/www/html && php bin/magento module:status"
```

### Logs einsehen
```bash
# System-Logs
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "tail -n 50 /var/www/html/var/log/system.log"

# Exception-Logs
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "tail -n 50 /var/www/html/var/log/exception.log"
```

## Performance-Überwachung
```bash
# Speichernutzung prüfen
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "free -h"

# Festplattennutzung
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "df -h"

# Top-Prozesse
ssh -i ~/.ssh/id_andre_sh root@165.22.66.230 "top -b -n 1 | head -20"
```

## Wichtige Hinweise
1. **Swap-Auslastung**: Der Server hat eine hohe Swap-Nutzung (100%), was auf Speicherprobleme hinweist
2. **Deployment Mode**: Production - Änderungen erfordern Kompilierung und Cache-Leerung
3. **Cron-Jobs**: Laufen automatisch für Wartungsaufgaben
4. **PIM-Integration**: Externe Produktdaten werden zum Magento-Shop synchronisiert

## Temporäre Änderungen (03.06.2025)
- **Elasticsearch deaktiviert**: Wegen Speichermangel auf MySQL-Suche umgestellt
- **Zusätzlicher Swap**: 2GB temporärer Swap hinzugefügt (/swapfile2)
- **Such-Engine**: Von `elasticsearch7` auf `mysql` geändert

## Nach Server-Upgrade erforderlich
1. **Elasticsearch/OpenSearch klären**:
   - Prüfen welche Such-Engine aktiv ist: `systemctl status elasticsearch opensearch`
   - Nur EINE Such-Engine verwenden (nicht beide!)
   - Empfehlung: OpenSearch verwenden (moderner, weniger Ressourcen)
2. **Such-Engine Konfiguration**:
   - Elasticsearch/OpenSearch Heap erhöhen: `-Xms1g -Xmx1g`
   - Magento Such-Engine zurückstellen: `php bin/magento config:set catalog/search/engine elasticsearch7`
   - Suchindex neu aufbauen: `php bin/magento indexer:reindex catalogsearch_fulltext`
3. **MySQL optimieren**: Buffer Pool auf 2G erhöhen

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

### API Credentials
- **Base URL**: http://165.22.66.230/rest/V1
- **API Token**: f62nq8643aw7v9nomc07g0g1lyj0afg9

## Nächste Schritte
- Performance-Optimierung aufgrund der hohen Swap-Nutzung
- MagnaLista-Konfiguration überprüfen
- PIM-Synchronisation analysieren