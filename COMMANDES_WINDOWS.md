# Commandes Windows pour le Projet

## 📁 Création de Dossiers

### PowerShell (Recommandé)
```powershell
# Créer le dossier QR codes
New-Item -ItemType Directory -Force -Path "storage\app\public\qr_codes"

# Créer le dossier livres
New-Item -ItemType Directory -Force -Path "storage\app\private\livres"
```

### CMD (Invite de commandes)
```cmd
mkdir storage\app\public\qr_codes
mkdir storage\app\private\livres
```

## ✅ Vérification

Vérifiez que les dossiers existent :
```powershell
Test-Path "storage\app\public\qr_codes"
Test-Path "storage\app\private\livres"
```

## 🚀 Commandes Laravel

Toutes les commandes Laravel fonctionnent normalement :
```bash
php artisan migrate
php artisan storage:link
php artisan migrate:fresh --seed
php artisan serve
```

## 📝 Note

Les commandes Linux/Mac (`mkdir -p`, `ls`, etc.) ne fonctionnent pas dans PowerShell/CMD Windows.
Utilisez les commandes PowerShell ou CMD équivalentes.

