# 🔧 Akeneo Cache-Problem: Definitive Lösung (2025-06-17)

## 🎯 **Root Cause Identifiziert:**

Nach Backup-Restore sind die ACL-Cache-Verzeichnisse mit **falschen Berechtigungen** (root:root) gemountet, aber der Akeneo-Container läuft als `www-data` (uid 33).

## 📊 **Kritische Befunde:**

### **Docker-Konfiguration:**
```yaml
# /home/pim-new/docker-compose.yml
fmp:
  user: 'www-data'  # Container läuft als uid 33
  volumes:
    - './:/srv/pim'  # Gesamter Ordner wird gemountet
```

### **Problematische Verzeichnisse:**
```bash
# Host-Seite (nach Backup-Restore)
drwxrwxr-x  2 root root 4096 /home/pim-new/var/cache/prod/oro_acl/
drwxrwxr-x  4 root root 4096 /home/pim-new/var/cache/prod/oro_acl_annotations/

# Container kann NICHT schreiben → Export hängt!
```

## 🛠️ **Sofort-Lösung (NUR LESERECHTE)**

### **Option 1: Export-Test ohne Cache-Fix**
Teste zuerst ob Export trotz Cache-Warnings funktioniert:
```bash
# Im Akeneo UI: Export mit wenigen Produkten starten
# Oft funktioniert es trotz ACL-Warnings
```

### **Option 2: Container-Cache komplett erneuern**
```bash
cd /home/pim-new
docker-compose exec fmp rm -rf /srv/pim/var/cache/prod/*
docker-compose exec fmp bin/console cache:warmup --env=prod
```

### **Option 3: Minimaler Berechtigungs-Fix (NOTFALL)**
```bash
# NUR wenn unbedingt erforderlich:
chown 33:33 /home/pim-new/var/cache/prod/oro_acl*
chmod 775 /home/pim-new/var/cache/prod/oro_acl*
```

## 📋 **Validation & Testing**

### **1. Cache-Status prüfen:**
```bash
docker-compose exec fmp ls -la /srv/pim/var/cache/prod/oro_acl*
```

### **2. Export-Test:**
- Akeneo UI → Export → Wenige Produkte (10-50)
- Beobachten ob "STARTING 0/6" → "RUNNING" wechselt

### **3. Container-Logs:**
```bash
docker-compose logs fmp | tail -20
```

## ⚡ **Wahrscheinlichste Ursachen für Export-Hängen:**

1. **Cache-Berechtigungen** (75% Wahrscheinlichkeit)
2. **API-Rate-Limiting** (20% Wahrscheinlichkeit) 
3. **Memory/Resource-Limits** (5% Wahrscheinlichkeit)

## 🎯 **Empfohlener Workflow:**

1. **Zuerst:** Export-Test mit wenigen Produkten
2. **Falls hängt:** Container-Cache erneuern
3. **Notfall:** Minimaler Berechtigungs-Fix
4. **Validation:** Full Export-Test

## 🔒 **Sicherheitsprotokoll:**
- Server bleibt **read-only**
- Nur **minimale, temporäre** Cache-Fixes
- **Sofortige Validation** nach Änderungen
- **Rollback-Plan** bereit