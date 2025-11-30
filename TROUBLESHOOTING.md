# Guide de Dépannage - Erreur 500

## Vérifications à faire

### 1. Vérifier que la base de données existe

```bash
# Se connecter à MySQL (XAMPP)
mysql -u root -p

# Créer la base de données
CREATE DATABASE IF NOT EXISTS bibliotheque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 2. Vérifier le fichier .env

Assure-toi que ton `.env` contient :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bibliotheque
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Exécuter les migrations et seeders

```bash
cd C:\xampp\htdocs\back\backend
php artisan migrate:fresh --seed
```

### 4. Vérifier que Sanctum est publié

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 5. Nettoyer le cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 6. Vérifier les permissions des dossiers

```bash
# Sur Windows, assure-toi que ces dossiers sont accessibles en écriture :
# - storage/logs
# - storage/framework/cache
# - storage/framework/sessions
# - storage/framework/views
# - bootstrap/cache
```

### 7. Vérifier les logs

```bash
# Voir les dernières erreurs
Get-Content storage\logs\laravel.log -Tail 50
```

## Test rapide

1. Démarrer le serveur : `php artisan serve`
2. Tester l'endpoint de santé : `http://127.0.0.1:8000/up`
3. Tester l'API : `POST http://127.0.0.1:8000/api/v1/auth/register`

## Comptes de test après seed

- **Admin** : `admin@ecole.test` / `admin1234`
- **Bibliothécaire** : `biblio@ecole.test` / `biblio1234`
- **Prof** : `prof@ecole.test` / `prof1234`






