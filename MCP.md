# MCP Server Installation

## Perplexity Ask

### KORRIGIERTE Installation (04.06.2025)
```bash
# Das offizielle Package @perplexity-ai/mcp-server-perplexity existiert NICHT mehr!
# Verwende stattdessen ein funktionierendes alternatives Package:
claude mcp add perplexity npx @jschuller/perplexity-mcp -e PERPLEXITY_API_KEY=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
```

### Alternative verfügbare Packages
```bash
# Option 1: @jschuller/perplexity-mcp (empfohlen)
claude mcp add perplexity npx @jschuller/perplexity-mcp -e PERPLEXITY_API_KEY=...

# Option 2: mcp-perplexity-search
claude mcp add perplexity npx mcp-perplexity-search -e PERPLEXITY_API_KEY=...

# Option 3: perplexity-mcp (original)
claude mcp add perplexity npx perplexity-mcp -e PERPLEXITY_API_KEY=...
```

### Manuelle Claude Desktop Konfiguration
Falls `claude mcp add` nicht funktioniert, direkt in `claude_desktop_config.json` eintragen:
```json
{
  "mcpServers": {
    "perplexity": {
      "command": "npx",
      "args": ["-y", "@jschuller/perplexity-mcp"],
      "env": {
        "PERPLEXITY_API_KEY": "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"
      }
    }
  }
}
```

### Troubleshooting
Wenn der Server "failed" ist, prüfen Sie:
1. **Package existiert nicht**: `@perplexity-ai/mcp-server-perplexity` ist nicht verfügbar
   - **Fehler**: `npm error 404 Not Found - '@perplexity-ai/mcp-server-perplexity@*' is not in this registry`
   - **Lösung**: Alternatives Package verwenden (siehe oben)
2. **Fehlender API-Key**: `Error: PERPLEXITY_API_KEY environment variable is required`
   - Lösung: API-Key muss mit `-e` Flag gesetzt werden
3. **Alte Installation vorhanden**: Server komplett entfernen und neu installieren:
   ```bash
   claude mcp remove perplexity
   claude mcp add perplexity npx @jschuller/perplexity-mcp -e PERPLEXITY_API_KEY=...
   ```
4. **Manueller Test**: `npx @jschuller/perplexity-mcp` (benötigt PERPLEXITY_API_KEY env var)

### Erkenntnisse (04.06.2025)
- **Problem**: Das ursprünglich dokumentierte Package `@perplexity-ai/mcp-server-perplexity` existiert nicht im npm Registry
- **Lösung**: Alternative Packages verwenden, z.B. `@jschuller/perplexity-mcp`
- **Installation ohne API-Key**: Führt zu "failed" Status und Fehlern
- **Claude Desktop Neustart**: Nach Konfigurationsänderungen erforderlich

## Context7
```bash
claude mcp add context7 npx -y @upstash/context7-mcp@latest
```

## Puppeteer
```bash
claude mcp add puppeteer npx -y @modelcontextprotocol/server-puppeteer
```

## Wichtige Hinweise
- **API-Keys**: Müssen mit `-e KEY=value` während der Installation übergeben werden
- **Syntax**: `claude mcp add <name> <command> [options]` (kein `--` vor dem Kommando)
- **Environment Variables**: Werden direkt beim `add` Befehl gesetzt, nicht separat

## Voraussetzungen
- Node.js 18+
- Claude Code CLI

## Verfügbare Befehle
```bash
claude mcp list                    # Alle MCP Server auflisten
claude mcp remove <name>           # MCP Server entfernen
claude mcp add --help              # Hilfe für Installation
```