# Marktplatz-Odoo Projekt

## Übersicht
Dieses Projekt dokumentiert die Integration eines Magento-Shops mit MagnaLista für Marktplatzanbindungen auf einem Ubuntu-Server bei DigitalOcean.

## Server-Informationen
- **Server IP**: 165.22.66.230
- **Provider**: DigitalOcean
- **Betriebssystem**: Ubuntu 22.04.5 LTS
- **CPU**: 2 Cores (DO-Premium-AMD)
- **RAM**: 3.8 GB
- **Speicher**: 78 GB (22 GB belegt)
- **SSH-Zugang**: root@165.22.66.230 (SSH-Key auf Mac konfiguriert)

## Technischer Stack

### Webserver & Runtime
- **Webserver**: Nginx 1.18.0
- **PHP**: 8.2.28 mit OPcache
- **MySQL**: 8.0.42

### Magento-Shop
- **Version**: Magento 2.4.7-p4
- **Deployment Mode**: Production
- **Installation**: /var/www/html
- **Module**: 235 aktivierte Module, 3 deaktivierte Module

### MagnaLista Add-on
- **Modul**: Redgecko_Magnalister (✓ Installiert und aktiviert)
- **Pfad**: /var/www/html/app/code/Redgecko/Magnalister/
- **Funktion**: Marktplatzanbindung für verschiedene Online-Marktplätze

### PIM (Product Information Management)
- Synchronisiert Produktdaten zum Magento-Shop
- Zentrale Produktdatenverwaltung

## Wichtige installierte Module
- FireGento_FastSimpleImport
- PayPal_Braintree
- Redgecko_Magnalister (MagnaLista)
- Webkul_ProductImportQueue

## Cron-Jobs
```bash
# Magento Cron (jede Minute)
* * * * * /usr/bin/php8.2 /var/www/html/bin/magento cron:run

# Indexer Reindex (alle 6 Stunden)
0 */6 * * * /usr/bin/php8.2 /var/www/html/bin/magento indexer:reindex

# Log Cleanup (täglich um 2 Uhr)
0 2 * * * /usr/bin/php8.2 /var/www/html/bin/magento log:clean --days 7
```

## Zugriff
```bash
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230
```

## Performance-Hinweis
Der Server zeigt eine hohe Swap-Auslastung (1.0 GB von 1.0 GB), was auf Speicherengpässe hindeuten könnte. Eine Überwachung der Performance wird empfohlen.

## Magento 2 MCP Server

### Installation
```bash
git clone https://github.com/boldcommerce/magento2-mcp.git
cd magento2-mcp && npm install
```

### Claude Code Konfiguration
```bash
claude mcp add magento2 /opt/homebrew/bin/node /path/to/magento2-mcp/mcp-server.js \
  --env MAGENTO_BASE_URL=http://165.22.66.230/rest/V1 \
  --env MAGENTO_API_TOKEN=f62nq8643aw7v9nomc07g0g1lyj0afg9
```

### Verfügbare MCP Tools (14)
- **Produkte (9)**: `get_product_by_sku`, `get_product_by_id`, `search_products`, `advanced_product_search`, `get_product_categories`, `get_related_products`, `get_product_stock`, `get_product_attributes`, `update_product_attribute`
- **Umsatz (4)**: `get_revenue`, `get_order_count`, `get_product_sales`, `get_revenue_by_country`
- **Kunden (1)**: `get_customer_ordered_products_by_email`