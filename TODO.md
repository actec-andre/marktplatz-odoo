# TODO: Magento Shop Optimierung und Fehlerbehebung

**Priorität**: 🔴 Kritisch | 🟠 Hoch | 🟡 Mittel | 🟢 Niedrig  
**Zeitrahmen**: Sofort (< 1h) | Kurzfristig (< 24h) | Mittelfristig (< 1 Woche)

## 🔴 KRITISCH - SOFORTMASSNAHMEN (< 1 Stunde)

### 1. Speicher-Notfall beheben
**Priorität**: 🔴 Kritisch  
**Zeitaufwand**: 15 Minuten  
**Befehle**:
```bash
# 1. Cache-Speicher freigeben
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "sync && echo 3 > /proc/sys/vm/drop_caches"

# 2. Alte Log-Dateien löschen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "find /var/www/html/var/log -name '*.log' -mtime +7 -delete"

# 3. Temporäre Dateien bereinigen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "find /var/www/html/var/tmp -type f -mtime +1 -delete"

# 4. MySQL temporäre Tabellen bereinigen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "mysql -e 'FLUSH TABLES;'"
```

### 2. Hängende Prozesse beenden
**Priorität**: 🔴 Kritisch  
**Zeitaufwand**: 10 Minuten  
**Befehle**:
```bash
# 1. Alle hängenden Magento Cron-Prozesse beenden
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "pkill -f 'magento cron:run' && sleep 5 && pkill -9 -f 'magento cron:run'"

# 2. PHP-FPM neustarten um Zombie-Prozesse zu beenden
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "systemctl restart php8.2-fpm"

# 3. Status prüfen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "ps aux | grep -c 'magento cron'"
```

### 3. Zusätzlichen Swap-Speicher hinzufügen (Temporär)
**Priorität**: 🔴 Kritisch  
**Zeitaufwand**: 5 Minuten  
**Befehle**:
```bash
# Zusätzliche 2GB Swap erstellen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 << 'EOF'
fallocate -l 2G /swapfile2
chmod 600 /swapfile2
mkswap /swapfile2
swapon /swapfile2
echo '/swapfile2 none swap sw 0 0' >> /etc/fstab
swapon --show
EOF
```

## 🟠 HOCH - KURZFRISTIG (< 24 Stunden)

### 4. MySQL Konfiguration optimieren
**Priorität**: 🟠 Hoch  
**Zeitaufwand**: 30 Minuten  
**Schritte**:

1. **Backup der aktuellen Konfiguration**:
```bash
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cp /etc/mysql/mysql.conf.d/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf.backup"
```

2. **Neue optimierte Konfiguration erstellen**:
```bash
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cat > /etc/mysql/mysql.conf.d/99-magento-optimized.cnf << 'EOF'
[mysqld]
# Speicher-Optimierungen
innodb_buffer_pool_size = 1G
innodb_buffer_pool_instances = 1
innodb_log_file_size = 256M
innodb_log_buffer_size = 16M

# Connection-Optimierungen
max_connections = 300
max_connect_errors = 1000000
connect_timeout = 10

# Query-Optimierungen
query_cache_type = 0
query_cache_size = 0
tmp_table_size = 64M
max_heap_table_size = 64M

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
log_queries_not_using_indexes = 1

# Weitere Optimierungen
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
EOF"
```

3. **MySQL neustarten**:
```bash
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "systemctl restart mysql && systemctl status mysql"
```

### 5. PHP Konfiguration anpassen
**Priorität**: 🟠 Hoch  
**Zeitaufwand**: 20 Minuten  
**Schritte**:

```bash
# PHP-FPM Konfiguration optimieren
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cat > /etc/php/8.2/fpm/conf.d/99-magento.ini << 'EOF'
; Speicher-Limits
memory_limit = 2G
max_execution_time = 18000
max_input_time = 60
max_input_vars = 10000

; Upload-Limits
post_max_size = 64M
upload_max_filesize = 64M

; Session
session.gc_maxlifetime = 86400

; OPcache
opcache.enable = 1
opcache.memory_consumption = 512
opcache.max_accelerated_files = 100000
opcache.validate_timestamps = 0
opcache.save_comments = 1
opcache.enable_cli = 1

; Sonstiges
realpath_cache_size = 32M
realpath_cache_ttl = 7200
EOF"

# PHP-FPM Pool anpassen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cat > /etc/php/8.2/fpm/pool.d/magento.conf << 'EOF'
[magento]
user = www-data
group = www-data
listen = /run/php/php8.2-fpm-magento.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
EOF"

# Neustarten
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "systemctl restart php8.2-fpm"
```

### 6. Redis Speicherlimit setzen
**Priorität**: 🟠 Hoch  
**Zeitaufwand**: 10 Minuten  
**Befehle**:
```bash
# Redis Konfiguration anpassen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 << 'EOF'
redis-cli CONFIG SET maxmemory 512mb
redis-cli CONFIG SET maxmemory-policy allkeys-lru
redis-cli CONFIG REWRITE
systemctl restart redis
EOF
```

### 7. Cron-Schedule bereinigen
**Priorität**: 🟠 Hoch  
**Zeitaufwand**: 15 Minuten  
**Befehle**:
```bash
# 1. Alte Einträge löschen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "mysql magento -e 'DELETE FROM cron_schedule WHERE scheduled_at < DATE_SUB(NOW(), INTERVAL 1 DAY);'"

# 2. Fehlgeschlagene Jobs zurücksetzen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "mysql magento -e 'UPDATE cron_schedule SET status=\"pending\" WHERE status=\"running\" AND executed_at < DATE_SUB(NOW(), INTERVAL 1 HOUR);'"

# 3. Cron neu installieren
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && sudo -u www-data php bin/magento cron:install --force"
```

### 8. Suchindex neu aufbauen
**Priorität**: 🟠 Hoch  
**Zeitaufwand**: 30-60 Minuten  
**Befehle**:
```bash
# Einzelnen Index neu aufbauen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && sudo -u www-data php bin/magento indexer:reindex catalogsearch_fulltext"

# Oder alle Indizes (dauert länger)
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && sudo -u www-data php bin/magento indexer:reindex"
```

## 🟡 MITTEL - MITTELFRISTIG (< 1 Woche)

### 9. Dependency Injection neu kompilieren
**Priorität**: 🟡 Mittel  
**Zeitaufwand**: 45 Minuten  
**Befehle**:
```bash
# 1. Maintenance Mode aktivieren
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && sudo -u www-data php bin/magento maintenance:enable"

# 2. DI kompilieren
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && sudo -u www-data php bin/magento setup:di:compile"

# 3. Static Content deployen (falls nötig)
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && sudo -u www-data php bin/magento setup:static-content:deploy -f"

# 4. Cache leeren
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && sudo -u www-data php bin/magento cache:flush"

# 5. Maintenance Mode deaktivieren
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cd /var/www/html && sudo -u www-data php bin/magento maintenance:disable"
```

### 10. Monitoring einrichten
**Priorität**: 🟡 Mittel  
**Zeitaufwand**: 1 Stunde  
**Schritte**:

1. **Basis-Monitoring-Script erstellen**:
```bash
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "cat > /root/monitor-magento.sh << 'EOF'
#!/bin/bash
# Magento Monitoring Script

echo \"=== Magento System Status === $(date)\"

# Memory
echo -e \"\\n--- Memory Status ---\"
free -h

# Load
echo -e \"\\n--- System Load ---\"
uptime

# Disk
echo -e \"\\n--- Disk Usage ---\"
df -h | grep -E \"^/dev/\"

# MySQL
echo -e \"\\n--- MySQL Connections ---\"
mysql -e \"SHOW STATUS LIKE 'Threads_connected';\"

# Cron Status
echo -e \"\\n--- Cron Status (Last 24h) ---\"
mysql magento -e \"SELECT status, COUNT(*) as count FROM cron_schedule WHERE scheduled_at > DATE_SUB(NOW(), INTERVAL 1 DAY) GROUP BY status;\"

# PHP-FPM
echo -e \"\\n--- PHP-FPM Processes ---\"
ps aux | grep php-fpm | wc -l

# Redis
echo -e \"\\n--- Redis Memory ---\"
redis-cli info memory | grep used_memory_human
EOF"

chmod +x /root/monitor-magento.sh
```

2. **Cron-Job für Monitoring einrichten**:
```bash
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "echo '0 * * * * /root/monitor-magento.sh >> /var/log/magento-monitor.log 2>&1' | crontab -"
```

### 11. Datenbank-Wartung
**Priorität**: 🟡 Mittel  
**Zeitaufwand**: 2 Stunden  
**Befehle**:
```bash
# 1. Datenbank-Backup erstellen
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "mysqldump magento | gzip > /backup/magento-$(date +%Y%m%d).sql.gz"

# 2. Tabellen optimieren
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "mysqlcheck -o magento"

# 3. Große Tabellen identifizieren
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 "mysql -e 'SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS \"Size in MB\" FROM information_schema.TABLES WHERE table_schema = \"magento\" ORDER BY (data_length + index_length) DESC LIMIT 20;'"
```

## 🟢 NIEDRIG - LANGFRISTIG

### 12. Server-Upgrade planen
**Priorität**: 🟢 Niedrig (aber wichtig!)  
**Empfehlung**:
- RAM von 4GB auf mindestens 8GB erhöhen
- Oder: Auf größere DigitalOcean Droplet migrieren
- Alternative: Separaten Datenbank-Server einrichten

### 13. Varnish Cache implementieren
**Priorität**: 🟢 Niedrig  
**Nutzen**: Drastische Performance-Verbesserung  
**Aufwand**: 4-8 Stunden

### 14. ElasticSearch einrichten
**Priorität**: 🟢 Niedrig  
**Nutzen**: Bessere Suchperformance  
**Hinweis**: Benötigt zusätzlichen RAM

## 📋 CHECKLISTE FÜR DIE AUSFÜHRUNG

- [ ] Backup vor jeder Änderung erstellen
- [ ] Monitoring während der Änderungen aktiv beobachten
- [ ] Nach jeder Änderung testen:
  - [ ] Frontend lädt
  - [ ] Admin-Bereich erreichbar
  - [ ] Bestellung kann durchgeführt werden
  - [ ] Suche funktioniert

## 🔄 REGELMÄSSIGE WARTUNG (Wöchentlich)

```bash
# Wartungs-Script
ssh -i ~/.ssh/id_ed25519 root@165.22.66.230 << 'EOF'
# Logs rotieren
find /var/www/html/var/log -name "*.log" -size +100M -exec truncate -s 0 {} \;

# Alte Cron-Einträge löschen
mysql magento -e "DELETE FROM cron_schedule WHERE scheduled_at < DATE_SUB(NOW(), INTERVAL 7 DAY);"

# Cache-Ordner bereinigen
find /var/www/html/var/cache -type f -mtime +7 -delete
find /var/www/html/var/page_cache -type f -mtime +7 -delete

# Reports löschen
find /var/www/html/var/report -type f -mtime +30 -delete
EOF
```

## ⚠️ WICHTIGE HINWEISE

1. **VOR JEDER ÄNDERUNG**: Vollständiges Backup erstellen
2. **MAINTENANCE MODE**: Bei größeren Änderungen aktivieren
3. **MONITORING**: System während Änderungen beobachten
4. **TESTEN**: Nach jeder Änderung Funktionalität prüfen
5. **DOKUMENTATION**: Alle Änderungen dokumentieren

## 📞 NOTFALL-KONTAKTE

- **Hosting**: DigitalOcean Support
- **Magento**: Adobe Commerce Support (falls Lizenz vorhanden)
- **MagnaLista**: Redgecko Support

---

**Letzte Aktualisierung**: 03.06.2025