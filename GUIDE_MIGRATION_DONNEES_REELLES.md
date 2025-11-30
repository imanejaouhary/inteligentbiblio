# Guide de Migration vers des Données Réelles

## 🎯 Objectif

Ce guide vous aide à nettoyer la base de données des données de test et à préparer le système pour de vraies données de production.

---

## 🧹 Étape 1 : Nettoyer les Données de Test

### Option A : Script Automatique (Windows)

```bash
cd backend
nettoyer-et-preparer.bat
```

### Option B : Commandes Manuelles

```bash
cd backend

# Nettoyer les données de test
php artisan db:seed --class=CleanDatabaseSeeder

# Ajouter des données minimales réelles
php artisan db:seed --class=RealDataSeeder
```

### Ce qui sera supprimé

- ✅ Tous les étudiants de test (60 étudiants)
- ✅ Tous les professeurs de test (3 professeurs)
- ✅ Tous les livres générés (55 livres)
- ✅ Tous les cours de test (9 cours)
- ✅ Tous les emprunts (151 emprunts)
- ✅ Toutes les évaluations (275 évaluations)
- ✅ Toutes les réclamations (36 réclamations)
- ✅ Tous les audit logs

### Ce qui sera conservé

- ✅ Compte Admin : `admin@ecole.test`
- ✅ Compte Bibliothécaire : `biblio@ecole.test`
- ✅ Structure de la base de données (toutes les tables)

---

## 📝 Étape 2 : Ajouter de Vraies Données

### 2.1 Ajouter des Utilisateurs

#### Via l'Interface Admin (Recommandé)

1. Se connecter en tant qu'admin
2. Aller dans la section "Utilisateurs"
3. Créer les utilisateurs un par un

#### Via l'API

```bash
POST /api/v1/auth/register
{
  "name": "Nom Complet",
  "email": "email@universite.ma",
  "password": "motdepasse123",
  "role": "etudiant",
  "filiere": "IL"
}
```

#### Via Tinker (Pour plusieurs utilisateurs)

```bash
php artisan tinker
```

```php
// Créer un étudiant
App\Models\User::create([
    'name' => 'Ahmed Benali',
    'email' => 'ahmed.benali@universite.ma',
    'password' => Hash::make('motdepasse123'),
    'role' => 'etudiant',
    'filiere' => 'IL',
]);

// Créer un professeur
App\Models\User::create([
    'name' => 'Professeur Fatima Alami',
    'email' => 'f.alami@universite.ma',
    'password' => Hash::make('motdepasse123'),
    'role' => 'prof',
    'filiere' => null,
]);
```

### 2.2 Ajouter des Livres

#### Via l'Interface Admin (Recommandé)

1. Se connecter en tant qu'admin
2. Aller dans "Livres" → "Ajouter un livre"
3. Remplir les informations :
   - Titre
   - Auteur
   - ISBN (unique)
   - Quantité
   - Description
   - Image (optionnel)

#### Via l'API

```bash
POST /api/v1/livres
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "titre": "Introduction à la Programmation",
  "auteur": "Jean Dupont",
  "isbn": "978-2-1234-5678-9",
  "quantite": 10,
  "description": "Un guide complet..."
}
```

#### Via Tinker (Pour plusieurs livres)

```php
$livres = [
    [
        'titre' => 'Base de Données',
        'auteur' => 'Marie Martin',
        'isbn' => '978-2-1234-5679-0',
        'quantite' => 8,
        'description' => 'Fondamentaux des bases de données',
    ],
    // ... plus de livres
];

foreach ($livres as $livre) {
    App\Models\Livre::create($livre);
}
```

### 2.3 Ajouter des Cours (Professeur)

Les professeurs peuvent ajouter des cours via l'interface ou l'API :

```bash
POST /api/v1/cours
Authorization: Bearer {prof_token}
Content-Type: multipart/form-data

titre: "Algorithmes et Structures de Données"
description: "Cours sur les algorithmes"
filiere: "IL"
fichier: [fichier.pdf]
```

---

## ✅ Étape 3 : Vérifier que Tout Fonctionne

### 3.1 Tester la Réservation

1. **Créer un étudiant** (via inscription ou admin)
2. **Se connecter en tant qu'étudiant**
3. **Réserver un livre** :
   ```bash
   POST /api/v1/reserve
   {
     "livre_id": 1
   }
   ```

4. **Vérifier la réponse** :
   - L'emprunt est créé
   - Le QR code est généré
   - `qr_code_url` est présent dans la réponse

5. **Télécharger le QR code** :
   ```bash
   GET /api/v1/emprunts/{id}/qr-code
   ```

### 3.2 Tester le Scanner QR (Bibliothécaire)

1. **Se connecter en tant que bibliothécaire**
2. **Scanner le QR code** :
   ```bash
   POST /api/v1/biblio/scan-qr-reservation
   {
     "qr_data": "{...données du QR code...}"
   }
   ```

### 3.3 Vérifier les Statistiques

```bash
# Admin
GET /api/v1/admin/stats

# Bibliothécaire
GET /api/v1/biblio/stats

# Étudiant
GET /api/v1/etudiant/stats
```

---

## 📊 Étape 4 : Migration de Données Existantes

Si vous avez déjà des données dans un autre système, vous pouvez les importer :

### 4.1 Importer des Utilisateurs depuis CSV

Créer un script d'import :

```php
// import_users.php
$csv = fopen('users.csv', 'r');
while (($data = fgetcsv($csv)) !== FALSE) {
    App\Models\User::create([
        'name' => $data[0],
        'email' => $data[1],
        'password' => Hash::make($data[2]),
        'role' => $data[3],
        'filiere' => $data[4] ?? null,
    ]);
}
fclose($csv);
```

### 4.2 Importer des Livres depuis CSV

```php
// import_livres.php
$csv = fopen('livres.csv', 'r');
while (($data = fgetcsv($csv)) !== FALSE) {
    App\Models\Livre::create([
        'titre' => $data[0],
        'auteur' => $data[1],
        'isbn' => $data[2],
        'quantite' => $data[3],
        'description' => $data[4] ?? null,
    ]);
}
fclose($csv);
```

---

## 🔒 Étape 5 : Sécurité et Configuration

### 5.1 Changer les Mots de Passe par Défaut

**Important** : Changez les mots de passe des comptes admin et bibliothécaire !

```bash
php artisan tinker
```

```php
$admin = App\Models\User::where('email', 'admin@ecole.test')->first();
$admin->password = Hash::make('nouveau_mot_de_passe_securise');
$admin->save();

$biblio = App\Models\User::where('email', 'biblio@ecole.test')->first();
$biblio->password = Hash::make('nouveau_mot_de_passe_securise');
$biblio->save();
```

### 5.2 Configuration de l'Email Universitaire

Si vous voulez restreindre les inscriptions aux emails universitaires, modifiez `RegisterRequest.php` :

```php
// backend/app/Http/Requests/Auth/RegisterRequest.php
'email' => [
    'required',
    'string',
    'email',
    'max:255',
    'unique:users,email',
    'regex:/^[a-zA-Z0-9._%+-]+@(universite\.ma|ecole\.ma)$/i',
],
```

### 5.3 Configuration de l'Environnement

Vérifiez votre fichier `.env` :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bibliotheque_prod
DB_USERNAME=votre_user
DB_PASSWORD=votre_password
```

---

## 📋 Checklist de Migration

- [ ] Nettoyer les données de test
- [ ] Changer les mots de passe par défaut
- [ ] Ajouter les vrais utilisateurs (étudiants, professeurs)
- [ ] Ajouter les vrais livres
- [ ] Tester une réservation complète
- [ ] Tester le QR code (génération et scan)
- [ ] Tester les livres numériques (upload et téléchargement)
- [ ] Vérifier les statistiques
- [ ] Configurer les emails universitaires (optionnel)
- [ ] Configurer l'environnement de production
- [ ] Sauvegarder la base de données

---

## 🚨 Points d'Attention

1. **Sauvegarde** : Faites une sauvegarde avant de nettoyer !
   ```bash
   mysqldump -u root -p bibliotheque_db > backup_avant_nettoyage.sql
   ```

2. **QR Codes** : Les emprunts existants n'auront pas de QR code. Utilisez `regenerate-qr` si nécessaire.

3. **Livres Numériques** : Les fichiers doivent être uploadés manuellement via l'interface admin.

4. **Images de Livres** : Les images doivent être uploadées via l'interface admin.

---

## 📞 Support

Si vous rencontrez des problèmes :

1. Vérifiez les logs : `storage/logs/laravel.log`
2. Vérifiez les permissions des dossiers
3. Vérifiez la connexion à la base de données
4. Vérifiez que toutes les migrations sont exécutées

---

**Bon courage pour la migration ! 🚀**

