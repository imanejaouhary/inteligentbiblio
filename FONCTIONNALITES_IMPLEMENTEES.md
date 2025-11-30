# Fonctionnalités Implémentées : QR Code et Livres Numériques

## ✅ Fonctionnalités Ajoutées

### 1. 📱 QR Code pour Réservations

#### Pour l'Étudiant
- **Génération automatique** : Un QR code est généré automatiquement lors de la réservation d'un livre
- **Téléchargement** : L'étudiant peut télécharger son QR code de réservation
- **Informations complètes** : Le QR code contient toutes les informations nécessaires :
  - ID de l'emprunt
  - Nom de l'étudiant
  - Titre et ISBN du livre
  - Dates d'emprunt et de retour prévue
  - Token de sécurité

#### Pour le Bibliothécaire
- **Scanner réservation** : Le bibliothécaire peut scanner le QR code pour valider une réservation
- **Scanner retour** : Le bibliothécaire peut scanner le QR code pour valider un retour
- **Validation automatique** : Le système vérifie automatiquement le token et valide l'opération

### 2. 📥 Téléchargement de Livres Numériques

#### Pour l'Étudiant
- **Téléchargement conditionnel** : L'étudiant peut télécharger un livre numérique seulement s'il a un emprunt actif
- **Formats supportés** : PDF, EPUB, MOBI
- **Sécurité** : Vérification des permissions avant chaque téléchargement

#### Pour l'Admin
- **Upload de fichiers** : L'admin peut uploader des fichiers numériques pour les livres
- **Gestion complète** : Peut activer/désactiver la disponibilité numérique

## 🔧 Modifications Techniques

### Migrations Créées

1. **`2025_01_15_000100_add_numerique_to_livres_table.php`**
   - `disponible_numerique` (boolean)
   - `fichier_path` (string, nullable)
   - `format` (enum: pdf, epub, mobi)
   - `taille_fichier` (bigInteger, nullable)

2. **`2025_01_15_000200_add_qr_code_to_emprunts_table.php`**
   - `reservation_token` (string, unique, nullable)
   - `qr_code_path` (string, nullable)
   - `qr_generated_at` (timestamp, nullable)

### Modèles Mis à Jour

- **Livre** : Ajout des champs pour les livres numériques
- **Emprunt** : Ajout des champs pour les QR codes

### Contrôleurs Modifiés

#### EmpruntController
- `reserve()` : Génère automatiquement un QR code
- `downloadQrCode()` : Télécharge le QR code (étudiant)
- `getQrCodeInfo()` : Récupère les infos du QR code

#### LivreController
- `download()` : Télécharge un livre numérique
- `uploadFile()` : Upload un fichier numérique (admin)

#### BibliothecaireController
- `scanQrReservation()` : Scanner et valider une réservation
- `scanQrRetour()` : Scanner et valider un retour

### Routes Ajoutées

```php
// QR Codes (Étudiant)
GET  /api/v1/emprunts/{id}/qr-code      // Télécharger le QR code
GET  /api/v1/emprunts/{id}/qr-info      // Infos du QR code

// Livres Numériques
GET  /api/v1/livres/{id}/download      // Télécharger un livre
POST /api/v1/livres/{id}/upload-file    // Upload fichier (admin)

// Scanner QR (Bibliothécaire)
POST /api/v1/biblio/scan-qr-reservation // Scanner réservation
POST /api/v1/biblio/scan-qr-retour      // Scanner retour
```

## 📋 Structure des Données QR Code

Le QR code contient un JSON avec les informations suivantes :

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

## 🔐 Sécurité

- **Token unique** : Chaque QR code a un token unique hashé (SHA-256)
- **Vérification** : Le token est vérifié à chaque scan
- **Permissions** : Seuls les étudiants peuvent voir leurs QR codes
- **Validation** : Seuls les bibliothécaires peuvent scanner et valider

## 📦 Service QR Code

Un service `QrCodeService` a été créé pour gérer la génération des QR codes :
- Utilise une API externe (`api.qrserver.com`) si GD n'est pas disponible
- Peut être remplacé par `simplesoftwareio/simple-qrcode` si GD est activé

## 🚀 Utilisation

### Pour l'Étudiant

1. **Réserver un livre** : Le QR code est généré automatiquement
2. **Voir le QR code** : `GET /api/v1/emprunts/{id}/qr-info`
3. **Télécharger le QR code** : `GET /api/v1/emprunts/{id}/qr-code`
4. **Télécharger un livre numérique** : `GET /api/v1/livres/{id}/download`

### Pour le Bibliothécaire

1. **Scanner une réservation** : 
   ```json
   POST /api/v1/biblio/scan-qr-reservation
   {
     "qr_data": "{...données du QR code...}"
   }
   ```

2. **Scanner un retour** :
   ```json
   POST /api/v1/biblio/scan-qr-retour
   {
     "qr_data": "{...données du QR code...}"
   }
   ```

### Pour l'Admin

1. **Uploader un fichier numérique** :
   ```bash
   POST /api/v1/livres/{id}/upload-file
   Content-Type: multipart/form-data
   
   fichier: [fichier.pdf]
   ```

## ⚠️ Notes Importantes

1. **Extension GD** : Pour utiliser `simplesoftwareio/simple-qrcode`, activez l'extension GD dans `php.ini`
2. **Storage** : Les QR codes sont stockés dans `storage/app/public/qr_codes/`
3. **Fichiers livres** : Les fichiers numériques sont stockés dans `storage/app/private/livres/`
4. **Permissions** : Assurez-vous que les dossiers de storage ont les bonnes permissions

## 🔄 Prochaines Étapes

1. **Frontend** : Implémenter l'affichage des QR codes dans l'interface
2. **Scanner** : Ajouter un scanner QR code dans l'interface bibliothécaire
3. **Notifications** : Notifier l'étudiant quand son QR code est généré
4. **Tests** : Tester tous les scénarios de scan et validation

## 📝 Exemple de Réponse API

### QR Code Info
```json
{
  "message": "Informations de réservation récupérées.",
  "data": {
    "emprunt": {
      "id": 1,
      "etudiant": {...},
      "livre": {...},
      "date_emprunt": "2025-01-15",
      "date_retour_prevue": "2025-01-29"
    },
    "qr_code_url": "http://localhost/storage/qr_codes/emprunt_1.png",
    "qr_generated_at": "2025-01-15T10:30:00Z"
  }
}
```

### Scan QR Réservation
```json
{
  "message": "QR code validé avec succès.",
  "data": {
    "emprunt": {...},
    "valide": true,
    "peut_valider": true
  }
}
```

