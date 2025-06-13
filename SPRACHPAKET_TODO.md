# Sprachpaket Installation - TODO

## 📋 Aufgabe
Deutsche Sprachpakete für Magento vollständig installieren

## 🎯 Status
- ✅ System bereits auf `de_DE` konfiguriert
- ✅ Statische Inhalte für `de_DE` teilweise vorhanden
- ❌ Sprachpaket noch nicht via Composer installiert

## 🛠️ Durchführung (Optional)

### Option 1: Vollständige Installation
```bash
# 1. Als user wechseln (nicht als root!)
su - user

# 2. Magento-Verzeichnis
cd /home/user/htdocs/srv866215.hstgr.cloud

# 3. Deutsches Sprachpaket installieren
composer require magento/language-de_de

# 4. Static Content für alle Sprachen generieren
php bin/magento setup:static-content:deploy

# 5. Cache leeren
php bin/magento cache:flush
```

### Option 2: Auf Englisch umstellen (Einfacher)
```bash
# 1. Als user wechseln (nicht als root!)
su - user

# 2. Magento-Verzeichnis
cd /home/user/htdocs/srv866215.hstgr.cloud

# 3. Sprache auf Englisch umstellen
php bin/magento config:set general/locale/code en_US

# 4. Cache leeren
php bin/magento cache:flush
```

## ⚠️ Risiko
- **Niedrig**: Nur Übersetzungen, keine Core-Funktionen
- **Reversibel**: Kann rückgängig gemacht werden
- **Modus**: `default` (nicht production)

## 📝 Notizen
- `de_DE` wird bei `setup:static-content:deploy` automatisch mit aktualisiert
- System funktioniert auch ohne Sprachpaket (dann auf Englisch)
- Kann jederzeit später nachgeholt werden

## ⚠️ **Sicherheitshinweise**
- **NIEMALS als root ausführen**: Alle Composer/Magento-Befehle als `user` ausführen
- **User-Rechte**: `user` (UID: 1002) hat bereits alle nötigen Berechtigungen
- **Pfad**: `/home/user/htdocs/srv866215.hstgr.cloud` ist der korrekte Magento-Root