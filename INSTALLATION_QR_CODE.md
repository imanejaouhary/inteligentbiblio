# Installation et Configuration QR Code

## Problème rencontré

Le package `simplesoftwareio/simple-qrcode` nécessite l'extension PHP **GD** qui n'est pas activée par défaut.

## Solutions

### Option 1 : Activer l'extension GD (Recommandée)

1. Ouvrez le fichier `php.ini` (généralement dans `C:\xampp\php\php.ini`)
2. Recherchez la ligne `;extension=gd`
3. Décommentez-la en supprimant le `;` : `extension=gd`
4. Redémarrez Apache dans XAMPP
5. Vérifiez avec : `php -m | findstr gd` (Windows) ou `php -m | grep gd` (Linux/Mac)

Ensuite, installez le package :
```bash
cd backend
composer require simplesoftwareio/simple-qrcode
```

### Option 2 : Utiliser l'implémentation actuelle (API externe)

L'implémentation actuelle utilise une API externe (`api.qrserver.com`) qui ne nécessite pas GD.

**Avantages** :
- Fonctionne immédiatement sans configuration
- Pas besoin d'extension PHP

**Inconvénients** :
- Dépend d'un service externe
- Nécessite une connexion Internet

### Option 3 : Générer le QR code côté frontend

Utiliser une bibliothèque JavaScript comme `qrcode.js` pour générer le QR code dans le navigateur.

**Avantages** :
- Pas de dépendance serveur
- Plus rapide
- Meilleure expérience utilisateur

**Inconvénients** :
- Les données doivent être envoyées au frontend
- Moins sécurisé (les données sont visibles côté client)

## Recommandation

**Option 1** est la meilleure pour la production car :
- Pas de dépendance externe
- Plus sécurisé
- Meilleure performance

Pour le développement, l'**Option 2** (actuelle) fonctionne bien.

## Vérification

Pour vérifier si GD est activé :
```bash
php -r "echo extension_loaded('gd') ? 'GD activé' : 'GD non activé';"
```

