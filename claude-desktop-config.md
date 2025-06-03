# Claude Desktop MCP Konfiguration für Magento 2

## Installation

1. Öffne Claude Desktop
2. Gehe zu Einstellungen → Developer
3. Klicke auf "Edit Config"
4. Füge folgende Konfiguration hinzu:

```json
{
  "mcpServers": {
    "magento2": {
      "command": "/opt/homebrew/bin/node",
      "args": ["/Users/andre/Documents/dev/claude/marktplatz-odoo/magento2-mcp/mcp-server.js"],
      "env": {
        "MAGENTO_BASE_URL": "http://165.22.66.230/rest/V1",
        "MAGENTO_API_TOKEN": "f62nq8643aw7v9nomc07g0g1lyj0afg9"
      }
    }
  }
}
```

5. Speichere die Datei
6. Starte Claude Desktop neu

## Verfügbare Tools

Der MCP Server bietet folgende Tools:

### Produkt-Tools
- `get_product_by_sku` - Produkt Details per SKU abrufen
- `get_product_by_id` - Produkt Details per ID abrufen
- `search_products` - Produkte suchen
- `advanced_product_search` - Erweiterte Produktsuche
- `get_product_categories` - Produkt-Kategorien abrufen
- `get_related_products` - Verwandte Produkte finden
- `get_product_stock` - Lagerbestand prüfen
- `get_product_attributes` - Produkt-Attribute abrufen
- `update_product_attribute` - Produkt-Attribute aktualisieren

### Umsatz & Bestellungen
- `get_revenue` - Umsatz für Zeitraum abrufen
- `get_order_count` - Anzahl Bestellungen abrufen
- `get_product_sales` - Verkaufsstatistiken
- `get_revenue_by_country` - Umsatz nach Land

### Kunden
- `get_customer_ordered_products_by_email` - Bestellte Produkte per E-Mail

## Beispiel-Anfragen an Claude

Nach der Installation kannst du Claude fragen:

- "Zeige mir die Umsätze der letzten Woche"
- "Wie viele Bestellungen hatten wir heute?"
- "Suche nach Produkten mit 'shirt' im Namen"
- "Zeige mir den Lagerbestand für SKU ABC123"
- "Welche Produkte hat kunde@example.com bestellt?"

## Hinweise

- Der Server läuft auf 165.22.66.230
- API Token: f62nq8643aw7v9nomc07g0g1lyj0afg9
- Base URL: http://165.22.66.230/rest/V1