# Claude Code Kontextdokumentation

## Projekt-Übersicht
Dieses Projekt verwaltet eine Magento 2.4.7 E-Commerce-Installation mit MagnaLista-Integration für Marktplatzanbindungen.

## Server-Zugang
- **Server**: root@165.22.66.230
- **SSH-Key**: ~/.ssh/id_ed25519
- **Verbindung**: `ssh -i ~/.ssh/id_ed25519 root@165.22.66.230`

## Wichtige Pfade
- **Magento-Root**: /var/www/html
- **MagnaLista-Modul**: /var/www/html/app/code/Redgecko/Magnalister/
- **Magento-CLI**: /var/www/html/bin/magento

## Häufige Befehle

### Magento-Status prüfen
```bash
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && php bin/magento --version"
```

### Cache leeren
```bash
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && php bin/magento cache:clean && php bin/magento cache:flush"
```

### Module auflisten
```bash
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && php bin/magento module:status"
```

### Logs einsehen
```bash
# System-Logs
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "tail -n 50 /var/www/html/var/log/system.log"

# Exception-Logs
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "tail -n 50 /var/www/html/var/log/exception.log"
```

## Performance-Überwachung
```bash
# Speichernutzung prüfen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "free -h"

# Festplattennutzung
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "df -h"

# Top-Prozesse
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "top -b -n 1 | head -20"
```

## Wichtige Hinweise
1. **Swap-Auslastung**: Der Server hat eine hohe Swap-Nutzung (100%), was auf Speicherprobleme hinweist
2. **Deployment Mode**: Production - Änderungen erfordern Kompilierung und Cache-Leerung
3. **Cron-Jobs**: Laufen automatisch für Wartungsaufgaben
4. **PIM-Integration**: Externe Produktdaten werden zum Magento-Shop synchronisiert

## Nächste Schritte
- Performance-Optimierung aufgrund der hohen Swap-Nutzung
- MagnaLista-Konfiguration überprüfen
- PIM-Synchronisation analysieren