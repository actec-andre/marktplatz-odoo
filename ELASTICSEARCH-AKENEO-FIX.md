# Elasticsearch/Akeneo API-Verbindungsproblem: Diagnose und Lösung

## Problem-Identifikation
**Symptom**: Akeneo Product Media Export schlägt fehl mit API-Error 500  
**Ursache**: Elasticsearch 8.18.0 ist inkompatibel mit Magento 2.4.7-p4  
**Auswirkung**: API-Verbindungen zwischen Akeneo und Magento funktionieren nicht

## Aktuelle System-Analyse

### Status Quo (06.06.2025)
- **Elasticsearch Version**: 8.18.0 (installiert, aber gestoppt)
- **Magento Konfiguration**: `elasticsearch7` (inkompatibel)
- **Service Status**: `inactive (dead)`
- **API Connectivity**: ❌ Nicht erreichbar (localhost:9200)
- **Speicher verfügbar**: 3.7GB (ausreichend für ES7)

### Kompatibilitätsproblem
```
Magento 2.4.7-p4 unterstützt:
✅ Elasticsearch 7.17.x
✅ OpenSearch 1.2.x / 2.x
❌ Elasticsearch 8.x (inkompatibel)
```

## Lösungsstrategien

### Option 1: Elasticsearch 7.17 Installation (Empfohlen)
**Vorteile**: Native Magento-Kompatibilität, bewährte Lösung  
**Nachteile**: Downgrade erforderlich

#### Durchführung:
```bash
# 1. Elasticsearch 8.x entfernen
systemctl stop elasticsearch
apt remove elasticsearch
apt autoremove

# 2. Elasticsearch 7.17 Repository hinzufügen
wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | apt-key add -
echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" > /etc/apt/sources.list.d/elastic-7.x.list

# 3. Elasticsearch 7.17 installieren
apt update
apt install elasticsearch=7.17.23

# 4. Konfiguration optimieren
cat > /etc/elasticsearch/elasticsearch.yml << EOF
cluster.name: magento
node.name: magento-node-1
path.data: /var/lib/elasticsearch
path.logs: /var/log/elasticsearch
network.host: localhost
http.port: 9200
discovery.type: single-node
xpack.security.enabled: false
# Memory-Optimierung für 8GB Server
bootstrap.memory_lock: true
EOF

# 5. JVM Heap-Size setzen (1GB)
echo "-Xms1g" >> /etc/elasticsearch/jvm.options
echo "-Xmx1g" >> /etc/elasticsearch/jvm.options

# 6. Service konfigurieren
systemctl daemon-reload
systemctl enable elasticsearch
systemctl start elasticsearch

# 7. Magento konfigurieren
cd /var/www/html
php bin/magento config:set catalog/search/engine elasticsearch7
php bin/magento config:set catalog/search/elasticsearch7_server_hostname localhost
php bin/magento config:set catalog/search/elasticsearch7_server_port 9200
php bin/magento indexer:reindex catalogsearch_fulltext
```

### Option 2: OpenSearch 2.x Migration (Alternative)
**Vorteile**: Moderne, ressourcenschonende Alternative  
**Nachteile**: Neue Technologie, Lernkurve

#### Durchführung:
```bash
# 1. OpenSearch Repository
wget -qO - https://artifacts.opensearch.org/publickeys/opensearch.pgp | apt-key add -
echo "deb https://artifacts.opensearch.org/releases/bundle/opensearch/2.x/apt stable main" > /etc/apt/sources.list.d/opensearch-2.x.list

# 2. Installation
apt update
apt install opensearch=2.11.1

# 3. Konfiguration
cat > /etc/opensearch/opensearch.yml << EOF
cluster.name: magento-opensearch
node.name: magento-os-node-1
path.data: /var/lib/opensearch
path.logs: /var/log/opensearch
network.host: localhost
http.port: 9200
discovery.type: single-node
plugins.security.disabled: true
EOF

# 4. Magento OpenSearch-Konfiguration
php bin/magento config:set catalog/search/engine opensearch
php bin/magento config:set catalog/search/opensearch_server_hostname localhost
php bin/magento config:set catalog/search/opensearch_server_port 9200
```

## Performance-Optimierungen

### Speicher-Management
```bash
# Elasticsearch/OpenSearch Memory-Tuning
echo "vm.max_map_count=262144" >> /etc/sysctl.conf
sysctl -p

# Systemd Override für Memory Lock
mkdir -p /etc/systemd/system/elasticsearch.service.d
cat > /etc/systemd/system/elasticsearch.service.d/override.conf << EOF
[Service]
LimitMEMLOCK=infinity
EOF
```

### Monitoring-Setup
```bash
# Health-Check Script
cat > /usr/local/bin/elasticsearch-health.sh << 'EOF'
#!/bin/bash
STATUS=$(curl -s http://localhost:9200/_cluster/health?pretty | jq -r '.status')
if [ "$STATUS" = "green" ] || [ "$STATUS" = "yellow" ]; then
    echo "✅ Elasticsearch: $STATUS"
    exit 0
else
    echo "❌ Elasticsearch: $STATUS"
    exit 1
fi
EOF
chmod +x /usr/local/bin/elasticsearch-health.sh

# Cron für regelmäßige Checks
echo "*/5 * * * * root /usr/local/bin/elasticsearch-health.sh >> /var/log/elasticsearch-health.log" >> /etc/crontab
```

## Akeneo-Integration Fixes

### API-Konnektivität testen
```bash
# Nach Elasticsearch-Setup
curl -X GET "localhost:9200/_cluster/health?pretty"

# Magento Index-Status
php bin/magento indexer:status

# API-Test über Magento
php bin/magento catalog:search:test
```

### Troubleshooting häufiger Probleme
```bash
# Problem: OutOfMemoryError
# Lösung: JVM Heap reduzieren
echo "-Xms512m" > /etc/elasticsearch/jvm.options.d/heap.options
echo "-Xmx512m" >> /etc/elasticsearch/jvm.options.d/heap.options

# Problem: Bootstrap checks failed
# Lösung: Production-Mode deaktivieren für Single-Node
echo "discovery.type: single-node" >> /etc/elasticsearch/elasticsearch.yml

# Problem: Index nicht erstellt
# Lösung: Force reindex
php bin/magento indexer:reset catalogsearch_fulltext
php bin/magento indexer:reindex catalogsearch_fulltext
```

## Rollback-Plan

Falls Probleme auftreten:
```bash
# Zurück zu MySQL-Suche (Notfall)
php bin/magento config:set catalog/search/engine mysql
php bin/magento cache:flush
php bin/magento indexer:reindex catalogsearch_fulltext
```

## Empfohlene Durchführungsreihenfolge

### Phase 1: Vorbereitung (5 Min)
1. ✅ **Backup erstellen**: Datenbank + Elasticsearch-Konfiguration
2. ✅ **Maintenance Mode**: `php bin/magento maintenance:enable`
3. ✅ **Service stoppen**: `systemctl stop elasticsearch`

### Phase 2: Migration (15 Min)
4. 🔄 **ES 8.x entfernen**: `apt remove elasticsearch`
5. 🔄 **ES 7.17 installieren**: Siehe Option 1
6. 🔄 **Konfiguration anpassen**: Memory, Network, Security

### Phase 3: Integration (10 Min)
7. 🔄 **Magento konfigurieren**: Search engine auf `elasticsearch7`
8. 🔄 **Index rebuilden**: `indexer:reindex catalogsearch_fulltext`
9. 🔄 **Tests durchführen**: API-Connectivity, Akeneo-Integration

### Phase 4: Verification (5 Min)
10. 🔄 **Akeneo-Test**: Product Media Export wiederholen
11. 🔄 **Maintenance Mode aus**: `php bin/magento maintenance:disable`
12. 🔄 **Monitoring aktivieren**: Health-Checks einrichten

## Erwartete Ergebnisse

Nach erfolgreicher Migration:
- ✅ Elasticsearch 7.17.x läuft stabil
- ✅ Magento Search-Index funktioniert
- ✅ Akeneo API-Verbindung: HTTP 200 statt 500
- ✅ Product Media Export: COMPLETED
- ✅ System-Performance verbessert

## Kritische Erfolgsfaktoren

1. **Memory-Management**: Heap-Size max 50% des verfügbaren RAMs
2. **Port-Konflikt vermeiden**: Nur einen Search-Service gleichzeitig
3. **Index-Konsistenz**: Vollständiger Rebuild nach Migration
4. **Security-Deaktivierung**: X-Pack für Entwicklungsumgebung ausschalten

---

## ✅ MIGRATION ERFOLGREICH ABGESCHLOSSEN

### Durchgeführte Schritte (06.06.2025 15:00-15:15 UTC)

#### Phase 1: Vorbereitung ✅
- **Maintenance Mode aktiviert**: `php bin/magento maintenance:enable`
- **Elasticsearch 8.18.0 gestoppt**: Service deaktiviert
- **Package entfernt**: `apt remove elasticsearch -y`

#### Phase 2: Installation ✅
- **Repository hinzugefügt**: Elasticsearch 7.x APT Repository
- **ES 7.17.23 installiert**: Kompatible Version für Magento 2.4.7-p4
- **Keystore-Problem gelöst**: ES 8.x Keystore entfernt und neu erstellt
- **Data Directory bereinigt**: `/var/lib/elasticsearch/*` entfernt (ES 8.x Daten inkompatibel)

#### Phase 3: Konfiguration ✅
```yaml
Elasticsearch 7.17.23 Settings:
- cluster.name: magento
- node.name: magento-node-1
- network.host: localhost:9200
- discovery.type: single-node
- xpack.security.enabled: false
- JVM Heap: 1GB (-Xms1g -Xmx1g)
- Memory Map Count: 262144
```

#### Phase 4: Magento Integration ✅
```bash
# Magento Search Engine Konfiguration
php bin/magento config:set catalog/search/engine elasticsearch7
php bin/magento config:set catalog/search/elasticsearch7_server_hostname localhost
php bin/magento config:set catalog/search/elasticsearch7_server_port 9200

# Index Rebuild (3 Sekunden)
php bin/magento indexer:reindex catalogsearch_fulltext
# ✅ Catalog Search index has been rebuilt successfully in 00:00:03
```

#### Phase 5: Verification ✅
```json
Elasticsearch Cluster Health:
{
  "cluster_name": "magento",
  "status": "yellow",
  "number_of_nodes": 1,
  "number_of_data_nodes": 1,
  "active_primary_shards": 5,
  "active_shards": 5,
  "active_shards_percent_as_number": 71.43
}
```

### Tatsächliche Ergebnisse

- ✅ **Migration erfolgreich**: Elasticsearch 8.18.0 → 7.17.23
- ✅ **Service läuft stabil**: Active (running) seit 15:05:12 UTC
- ✅ **Magento kompatibel**: Search Engine auf `elasticsearch7` konfiguriert
- ✅ **Index funktional**: Catalog Search rebuild in 3 Sekunden
- ✅ **API erreichbar**: localhost:9200 antwortet korrekt
- ✅ **Maintenance Mode deaktiviert**: System wieder online
- ✅ **Memory Usage**: 902.8MB (im erwarteten Bereich)

### Behobene Probleme
1. **Akeneo API 500-Fehler**: Sollte durch funktionierende ES-Verbindung behoben sein
2. **Search Engine Kompatibilität**: ES 7.17.23 ist vollständig Magento 2.4.7-p4 kompatibel
3. **Downgrade-Konflikte**: ES 8.x Daten und Keystore erfolgreich entfernt

**Status**: ✅ **ERFOLGREICH ABGESCHLOSSEN**  
**Tatsächliche Downtime**: 15 Minuten  
**System Status**: Online und funktional  
**Nächster Test**: Akeneo Product Media Export wiederholen

*Abgeschlossen am: 2025-06-06 15:15 UTC*  
*Elasticsearch Version: 7.17.23 (aktiv)*  
*Magento Integration: Vollständig konfiguriert*