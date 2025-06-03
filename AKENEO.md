# Akeneo PIM Integration

## Übersicht
Dieses Dokument beschreibt die Akeneo PIM Integration mit dem Magento 2.4.7 Shop.

## Akeneo-Setup
- **Version**: Akeneo 7
- **Connector**: Magento 2 Akeneo Connector v4.1.0
- **Synchronisation**: Push-basiert (Akeneo → Magento)

## Magento-Setup

### Installierte Module
- **Webkul_ProductImportQueue** v4.0.3
  - Pfad: `/app/code/Webkul/ProductImportQueue`
  - Funktion: Empfänger-Modul für Akeneo-Daten

### API-Endpoints
Die folgenden REST API Endpoints sind verfügbar:

| Endpoint | Methode | Funktion |
|----------|---------|----------|
| `/rest/V1/process-csv` | POST | CSV-Datenimport verarbeiten |
| `/rest/V1/before-process-csv` | POST | Vorverarbeitung für CSV-Import |
| `/rest/V1/products/attributes/{code}/options` | PUT/POST | Attribut-Optionen verwalten |
| `/rest/V1/categories` | POST | Kategorien erstellen |
| `/rest/V1/categories/{id}` | PUT | Kategorien aktualisieren |

### OAuth Integration
- **Name**: akeneo_marktplatz
- **Status**: Aktiv
- **API Token**: `f62nq8643aw7v9nomc07g0g1lyj0afg9`
- **Erstellt**: 2025-04-08

## Konfiguration für Akeneo

### API-Verbindung
```
Base URL: https://165.22.66.230/rest/V1/
Authorization: Bearer f62nq8643aw7v9nomc07g0g1lyj0afg9
Content-Type: application/json
```

### Wichtige Hinweise
1. **HTTPS erforderlich**: Der Server leitet HTTP auf HTTPS um
2. **SSL-Zertifikat**: Selbst-signiert - SSL-Verifikation in Akeneo deaktivieren
3. **Varnish/Proxy**: Route `/rest/V1/process-csv` sollte vom Cache ausgeschlossen werden

## Synchronisations-Flow
1. Akeneo sendet Daten via REST API an Magento
2. ProductImportQueue verarbeitet die Daten
3. Produkte, Kategorien und Attribute werden in Magento erstellt/aktualisiert

## Fehlerbehebung

### API-Test
```bash
# Test des process-csv Endpoints
curl -k -X POST "https://165.22.66.230/rest/V1/process-csv" \
  -H "Authorization: Bearer f62nq8643aw7v9nomc07g0g1lyj0afg9" \
  -H "Content-Type: application/json" \
  -d '{"data": {}}'
```

### Logs prüfen
```bash
# Magento Logs
tail -f /var/www/html/var/log/system.log
tail -f /var/www/html/var/log/exception.log

# API Logs
tail -f /var/www/html/var/log/webapi.log
```

## Status
✅ **Bereit für Synchronisation** - Alle notwendigen Komponenten sind installiert und konfiguriert.