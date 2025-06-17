# 📊 Akeneo Cache-Berechtigung Analyse (Nach Backup-Restore)

## 🚨 **Hauptproblem: Docker Volume Permission Mismatch**

### **Root Cause:**
Nach Backup-Restore sind die Cache-Verzeichnisse mit **falschen Benutzer-/Gruppenrechten** gemountet, wodurch der Akeneo-Container (www-data) **NICHT** in die ACL-Verzeichnisse schreiben kann.

## 📁 **Cache-Verzeichnis Analyse**

### **1. Host-System Berechtigungen (/home/pim-new/var/cache/prod/)**

```bash
# Hauptverzeichnis - KORREKT
drwxrwxrwx 10 root     root    4096 May 23 08:14 .

# ACL-Verzeichnisse - KORREKT  
drwxrwxr-x  2 root     root    4096 May 23 08:14 oro_acl
drwxrwxr-x  4 root     root    4096 May 23 08:14 oro_acl_annotations

# ACL-Cache-Dateien - KORREKT (www-data:pim)
total 752
drwxrwxr-x  2 www-data pim 53248 May 23 08:16 .
-rw-rw-r--  1 www-data pim   388 May 23 08:16 [sf2_acl_*].doctrinecache.data
```

### **2. Container-Mount Problem** 

**Docker Volume Mount:** `/home/pim-new/var/cache/prod/` → `/srv/pim/var/cache/prod/`

**Problem:** Die oro_acl-Verzeichnisse sind auf Host als `root:root` gemountet, aber der Container läuft als `www-data` (uid 33).

## 🐳 **Docker-Architektur Analyse**

### **Aktive Container:**
```
pim-new_fmp_1          # PHP-FPM (Hauptanwendung)  
pim-new_httpd_1        # Apache Webserver
pim-new_mysql_1        # Datenbank
pim-new_elasticsearch_1 # Suchindex
```

### **Volume-Mapping (Kritisch):**
```yaml
volumes:
  - ./var:/srv/pim/var
  - ./app:/srv/pim/app
  - ./src:/srv/pim/src
```

**Problem:** Host-Verzeichnis `./var` (root:root) wird direkt in Container `/srv/pim/var` (www-data) gemountet.

## ⚠️ **Berechtigungs-Konflikte**

| Komponente | Host (außen) | Container (innen) | Problem |
|------------|--------------|-------------------|---------|
| **oro_acl/** | `root:root` (755) | `www-data:www-data` erwartet | ❌ **Keine Schreibrechte** |
| **oro_acl_annotations/** | `root:root` (755) | `www-data:www-data` erwartet | ❌ **Keine Schreibrechte** |
| **ACL-Cache-Dateien** | `www-data:pim` (664) | `www-data:www-data` | ✅ **Funktioniert** |

## 🔧 **Saubere Lösung (Read-Only Empfehlung)**

### **Option 1: Host-Berechtigungen korrigieren (EMPFOHLEN)**
```bash
# NUR wenn erforderlich - mit Vorsicht!
sudo chown -R www-data:pim /home/pim-new/var/cache/prod/oro_acl*
sudo chmod -R 775 /home/pim-new/var/cache/prod/oro_acl*
```

### **Option 2: Docker User-Mapping fix**
```bash
# docker-compose.yml anpassen:
user: "33:1000"  # www-data:pim
```

### **Option 3: Cache-Warmup aus Container**
```bash
cd /home/pim-new
docker-compose exec fmp bin/console cache:warmup --env=prod
docker-compose exec fmp bin/console cache:clear --env=prod
```

## 📋 **Funktions-Validation**

### **Aktueller Status:**
- ✅ **ACL-Cache-Dateien:** Können gelesen werden (www-data:pim)
- ❌ **ACL-Verzeichnisse:** Können NICHT beschrieben werden (root:root)
- ❌ **Export-Funktionen:** Hängen wegen Cache-Schreibfehlern

### **Kritische Verzeichnisse:**
```
/home/pim-new/var/cache/prod/oro_acl/           # ACL-Cache
/home/pim-new/var/cache/prod/oro_acl_annotations/ # ACL-Annotations
/srv/pim/var/cache/prod/oro_acl/               # Container-Sicht
```

## 🎯 **Empfohlene Sofort-Maßnahmen**

### **1. Export-Test (Sicherster Ansatz):**
Teste Export trotz Cache-Warnungen - manchmal funktioniert es dennoch.

### **2. Container-Cache-Rebuild:**
```bash
cd /home/pim-new
docker-compose exec fmp rm -rf /srv/pim/var/cache/prod/oro_acl*
docker-compose exec fmp bin/console cache:warmup --env=prod
```

### **3. Notfall-Berechtigung-Fix:**
```bash
# NUR wenn unbedingt erforderlich:
sudo chown 33:1000 /home/pim-new/var/cache/prod/oro_acl*
sudo chmod 775 /home/pim-new/var/cache/prod/oro_acl*
```

## 📝 **Dokumentierte Cache-Struktur**

### **Symfony ACL-Cache-System:**
- **oro_acl/**: Haupt-ACL-Berechtigungen
- **oro_acl_annotations/**: Annotations-basierte ACL-Regeln  
- **Doctrine-Cache**: `.doctrinecache.data` Dateien für Performance

### **Docker Volume-Problem:**
Das ist ein **strukturelles Docker-Problem**, das durch unterschiedliche User-IDs zwischen Host und Container entsteht. Die sauberste Lösung wäre eine Anpassung der docker-compose.yml mit korrekten User-Mappings.

## ⚡ **Quick-Fix für Testing:**
```bash
# Test ob Export trotz Warnings funktioniert
# Export-Funktion in Akeneo PIM verwenden
# Falls Hängen anhält → Container-Cache löschen und neu warmup
```

## 🔒 **Sicherheitshinweis**
**Dieser Server sollte NUR Leserechte haben.** Berechtigungsänderungen nur nach expliziter Freigabe und mit minimalen, temporären Eingriffen.