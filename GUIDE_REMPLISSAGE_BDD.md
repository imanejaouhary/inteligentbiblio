# Guide de Remplissage de la Base de Données

## 🚀 Démarrage Rapide

### Étape 1 : Vérifier la configuration

Assurez-vous que votre fichier `.env` est correctement configuré :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nom_de_votre_base
DB_USERNAME=root
DB_PASSWORD=
```

### Étape 2 : Créer la base de données

1. Ouvrez **phpMyAdmin** (via XAMPP)
2. Créez une nouvelle base de données (ex: `bibliotheque_db`)
3. Ou utilisez MySQL en ligne de commande :
   ```sql
   CREATE DATABASE bibliotheque_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

### Étape 3 : Exécuter les migrations et seeders

Ouvrez un terminal dans le dossier `backend` et exécutez :

```bash
# Option 1 : Réinitialiser complètement (supprime toutes les données existantes)
php artisan migrate:fresh --seed

# Option 2 : Si vous avez déjà des données importantes, utilisez :
php artisan migrate
php artisan db:seed
```

### Étape 4 : Vérifier les données

Après l'exécution, vous devriez avoir :

- **1 Admin** : `admin@ecole.test` / `admin1234`
- **1 Bibliothécaire** : `biblio@ecole.test` / `biblio1234`
- **3 Professeurs** : `prof@ecole.test` / `prof1234` + 2 autres
- **40 Étudiants** : 20 en IL + 20 en ADIA
- **30 Livres** avec différentes quantités
- **9 Cours** (3 par professeur)
- **Emprunts** variés (en cours, retournés, en retard)
- **Évaluations** de livres
- **Réclamations** avec différents statuts

---

## 📊 Données Générées

### Utilisateurs

| Rôle | Nombre | Exemples d'emails |
|------|--------|-------------------|
| Admin | 1 | `admin@ecole.test` |
| Bibliothécaire | 1 | `biblio@ecole.test` |
| Professeurs | 3 | `prof@ecole.test` + 2 autres |
| Étudiants IL | 20 | Générés aléatoirement |
| Étudiants ADIA | 20 | Générés aléatoirement |

**Mot de passe par défaut pour les comptes de test** : `password`

### Livres

- **30 livres** avec :
  - Titres variés
  - Auteurs différents
  - ISBN uniques
  - Quantités entre 1 et 10
  - Descriptions

### Cours

- **9 cours** au total (3 par professeur)
- Répartis entre les filières IL et ADIA
- Avec fichiers PDF (simulés)

### Emprunts

- Chaque étudiant a **jusqu'à 3 emprunts**
- Statuts variés : `en_cours`, `retourne`, `retard`, `en_attente_retour`
- Dates d'emprunt sur les 20 derniers jours

### Évaluations

- **5 évaluations par livre** en moyenne
- Notes entre 3 et 5 étoiles
- Commentaires optionnels

### Réclamations

- **1 réclamation par étudiant** en moyenne
- Statuts variés : `en_attente`, `en_cours`, `resolu`

---

## 🔧 Commandes Utiles

### Réinitialiser complètement la base

```bash
php artisan migrate:fresh --seed
```

### Réinitialiser et réexécuter un seeder spécifique

```bash
php artisan migrate:fresh
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=LivreSeeder
# etc.
```

### Vider une table spécifique

```sql
-- Dans phpMyAdmin ou MySQL
TRUNCATE TABLE emprunts;
TRUNCATE TABLE evaluations;
TRUNCATE TABLE reclamations;
```

Puis réexécuter les seeders :
```bash
php artisan db:seed --class=EmpruntSeeder
php artisan db:seed --class=EvaluationSeeder
php artisan db:seed --class=ReclamationSeeder
```

### Ajouter plus de données

```bash
# Créer 50 livres supplémentaires
php artisan tinker
>>> App\Models\Livre::factory()->count(50)->create();
```

---

## 🎯 Comptes de Test Recommandés

### Pour tester en tant qu'Admin
- **Email** : `admin@ecole.test`
- **Mot de passe** : `admin1234`

### Pour tester en tant que Bibliothécaire
- **Email** : `biblio@ecole.test`
- **Mot de passe** : `biblio1234`

### Pour tester en tant que Professeur
- **Email** : `prof@ecole.test`
- **Mot de passe** : `prof1234`

### Pour tester en tant qu'Étudiant
- Connectez-vous avec n'importe quel étudiant généré
- Ou créez-en un nouveau via l'interface d'inscription

---

## ⚠️ Problèmes Courants

### Erreur : "Base de données n'existe pas"
```bash
# Créez la base de données d'abord dans phpMyAdmin
# Puis exécutez :
php artisan migrate
```

### Erreur : "Table already exists"
```bash
# Utilisez migrate:fresh pour tout réinitialiser
php artisan migrate:fresh --seed
```

### Erreur : "Foreign key constraint fails"
```bash
# Assurez-vous d'exécuter les seeders dans l'ordre :
# 1. UserSeeder (utilisateurs)
# 2. LivreSeeder (livres)
# 3. CoursSeeder (cours - nécessite des profs)
# 4. EmpruntSeeder (emprunts - nécessite étudiants et livres)
# 5. EvaluationSeeder (évaluations)
# 6. ReclamationSeeder (réclamations)
```

### Les données ne s'affichent pas
1. Vérifiez que les migrations ont bien été exécutées : `php artisan migrate:status`
2. Vérifiez les logs : `tail -f storage/logs/laravel.log`
3. Videz le cache : `php artisan cache:clear`

---

## 📝 Personnaliser les Seeders

Si vous voulez modifier les données générées, éditez les fichiers dans :
```
backend/database/seeders/
```

Par exemple, pour ajouter plus de livres :
```php
// Dans LivreSeeder.php
Livre::factory()->count(100)->create(); // Au lieu de 30
```

---

## ✅ Vérification

Après avoir exécuté les seeders, vérifiez dans phpMyAdmin ou avec :

```bash
php artisan tinker
>>> App\Models\User::count(); // Devrait retourner 45
>>> App\Models\Livre::count(); // Devrait retourner 30
>>> App\Models\Emprunt::count(); // Devrait retourner environ 120
```

---

## 🎉 C'est prêt !

Une fois les seeders exécutés, vous pouvez :
1. Démarrer le serveur : `php artisan serve`
2. Démarrer le frontend : `npm run dev` (dans le dossier frontend)
3. Vous connecter avec les comptes de test

**Bon développement ! 🚀**

