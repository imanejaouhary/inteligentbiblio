# Guide de Démarrage : QR Code et Livres Numériques

## 🚀 Installation Rapide

### 1. Exécuter les Migrations

```bash
cd backend
php artisan migrate
```

Cela ajoutera les colonnes nécessaires pour :
- Les livres numériques (fichier_path, format, etc.)
- Les QR codes (reservation_token, qr_code_path, etc.)

### 2. Créer le Lien Symbolique pour le Storage

```bash
php artisan storage:link
```

Cela permet d'accéder aux fichiers publics (QR codes, images) via l'URL.

### 3. Créer les Dossiers Nécessaires

```bash
# Windows (PowerShell)
New-Item -ItemType Directory -Force -Path "storage\app\public\qr_codes"
New-Item -ItemType Directory -Force -Path "storage\app\private\livres"

# Linux/Mac
mkdir -p storage/app/public/qr_codes
mkdir -p storage/app/private/livres
```

## 📱 Utilisation des QR Codes

### Pour l'Étudiant

1. **Réserver un livre** : 
   - Le QR code est généré automatiquement
   - L'étudiant reçoit l'ID de l'emprunt dans la réponse

2. **Voir les informations du QR code** :
   ```
   GET /api/v1/emprunts/{id}/qr-info
   Authorization: Bearer {token}
   ```

3. **Télécharger le QR code** :
   ```
   GET /api/v1/emprunts/{id}/qr-code
   Authorization: Bearer {token}
   ```

### Pour le Bibliothécaire

1. **Scanner une réservation** :
   ```
   POST /api/v1/biblio/scan-qr-reservation
   Authorization: Bearer {token}
   Content-Type: application/json
   
   {
     "qr_data": "{...données JSON du QR code...}"
   }
   ```

2. **Scanner un retour** :
   ```
   POST /api/v1/biblio/scan-qr-retour
   Authorization: Bearer {token}
   Content-Type: application/json
   
   {
     "qr_data": "{...données JSON du QR code...}"
   }
   ```

## 📥 Utilisation des Livres Numériques

### Pour l'Admin

1. **Uploader un fichier numérique pour un livre** :
   ```
   POST /api/v1/livres/{id}/upload-file
   Authorization: Bearer {token}
   Content-Type: multipart/form-data
   
   fichier: [fichier.pdf ou .epub ou .mobi]
   ```

   Le livre sera automatiquement marqué comme disponible en numérique.

### Pour l'Étudiant

1. **Télécharger un livre numérique** :
   ```
   GET /api/v1/livres/{id}/download
   Authorization: Bearer {token}
   ```

   **Condition** : L'étudiant doit avoir un emprunt actif pour ce livre.

## 🧪 Test Rapide

### 1. Créer un emprunt (étudiant)

```bash
POST /api/v1/reserve
{
  "livre_id": 1
}
```

Réponse inclut l'ID de l'emprunt créé.

### 2. Récupérer le QR code

```bash
GET /api/v1/emprunts/{emprunt_id}/qr-info
```

### 3. Scanner le QR code (bibliothécaire)

Copiez les données JSON du QR code et envoyez-les :

```bash
POST /api/v1/biblio/scan-qr-reservation
{
  "qr_data": "{\"type\":\"reservation\",\"emprunt_id\":1,...}"
}
```

## 📋 Structure des Données QR Code

Le QR code contient un JSON avec ces informations :

```json
{
  "type": "reservation",
  "emprunt_id": 1,
  "token": "abc123...",
  "etudiant_id": 5,
  "etudiant_nom": "Ahmed Benali",
  "livre_id": 10,
  "livre_titre": "Introduction à la Programmation",
  "livre_isbn": "978-2-1234-5678-9",
  "date_emprunt": "2025-01-15",
  "date_retour_prevue": "2025-01-29",
  "timestamp": "2025-01-15T10:30:00Z"
}
```

## ⚠️ Notes Importantes

1. **QR Code Service** : Utilise actuellement une API externe (`api.qrserver.com`)
   - Pour utiliser `simplesoftwareio/simple-qrcode`, activez l'extension GD dans `php.ini`
   - Voir `INSTALLATION_QR_CODE.md` pour plus de détails

2. **Permissions Storage** :
   - `storage/app/public/` : Accessible publiquement (QR codes)
   - `storage/app/private/` : Protégé (livres numériques)

3. **Sécurité** :
   - Les tokens sont hashés (SHA-256) dans la base de données
   - Seuls les étudiants peuvent voir leurs propres QR codes
   - Seuls les bibliothécaires peuvent scanner et valider

## 🔍 Vérification

Pour vérifier que tout fonctionne :

1. **Vérifier les migrations** :
   ```bash
   php artisan migrate:status
   ```

2. **Vérifier le storage** :
   ```bash
   ls storage/app/public/qr_codes/
   ls storage/app/private/livres/
   ```

3. **Tester une réservation** :
   - Connectez-vous en tant qu'étudiant
   - Réservez un livre
   - Vérifiez que le QR code est généré

## 🎯 Prochaines Étapes

1. **Frontend** : Implémenter l'affichage des QR codes
2. **Scanner** : Ajouter un scanner QR code dans l'interface
3. **Tests** : Tester tous les scénarios

## 📞 Support

Si vous rencontrez des problèmes :
1. Vérifiez les logs : `storage/logs/laravel.log`
2. Vérifiez les permissions des dossiers
3. Vérifiez que le storage:link est créé

