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