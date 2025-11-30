# Comptes Étudiants pour Tests

## 🔑 Informations de Connexion

**Mot de passe par défaut pour tous les étudiants** : `password`

---

## 👨‍🎓 Comptes Étudiants Recommandés pour Tests

### Filière IL (Informatique et Logique)

#### 1. Giuseppe Waelchi
- **Email** : `farrell.valentina@example.org`
- **Filière** : IL
- **ID** : 6
- **Emprunts** : 2
- **Réclamations** : 1
- **Statut** : Actif avec emprunts

#### 2. Devin Muller
- **Email** : `hartmann.mittie@example.net`
- **Filière** : IL
- **ID** : 7
- **Emprunts** : 3
- **Réclamations** : 1
- **Statut** : Actif avec plusieurs emprunts

#### 3. Max Heller
- **Email** : `jcassin@example.org`
- **Filière** : IL
- **ID** : 10
- **Emprunts** : 4
- **Réclamations** : 0
- **Statut** : Actif, beaucoup d'emprunts

#### 4. Salvatore Wisozk III
- **Email** : `harris.jaclyn@example.org`
- **Filière** : IL
- **ID** : 9
- **Emprunts** : 4
- **Réclamations** : 1
- **Statut** : Actif avec emprunts et réclamation

#### 5. Loy Bailey
- **Email** : `hamill.abbie@example.com`
- **Filière** : IL
- **ID** : 13
- **Emprunts** : 1
- **Réclamations** : 0
- **Statut** : Actif, peu d'emprunts (bon pour tester nouvelles réservations)

---

### Filière ADIA (Analyse et Développement d'Applications)

Pour voir les étudiants ADIA, exécutez :
```bash
php get_etudiants.php
```

Ou utilisez n'importe quel étudiant avec `filiere = 'ADIA'` dans la base de données.

---

## 📊 Statistiques Globales

- **Total étudiants** : 60
- **Filière IL** : 30 étudiants
- **Filière ADIA** : 30 étudiants
- **Emprunts totaux** : 151 emprunts
- **Réclamations totales** : 36 réclamations

---

## 🧪 Scénarios de Test Recommandés

### Test 1 : Nouvelle Réservation avec QR Code
**Utiliser** : Loy Bailey (`hamill.abbie@example.com`)
- Peu d'emprunts, idéal pour tester une nouvelle réservation
- QR code sera généré automatiquement

### Test 2 : Étudiant avec Plusieurs Emprunts
**Utiliser** : Max Heller (`jcassin@example.org`)
- 4 emprunts actifs
- Parfait pour tester la liste des emprunts
- Tester le téléchargement de plusieurs QR codes

### Test 3 : Étudiant avec Réclamation
**Utiliser** : Giuseppe Waelchi (`farrell.valentina@example.org`)
- A une réclamation en cours
- Parfait pour tester le système de réclamations

### Test 4 : Téléchargement Livre Numérique
**Utiliser** : N'importe quel étudiant avec un emprunt actif
1. Réserver un livre
2. Admin upload un fichier numérique
3. Étudiant télécharge le livre

---

## 🔍 Comment Trouver Plus d'Étudiants

### Via Tinker
```bash
php artisan tinker
>>> App\Models\User::where('role', 'etudiant')->where('filiere', 'IL')->get(['name', 'email']);
>>> App\Models\User::where('role', 'etudiant')->where('filiere', 'ADIA')->get(['name', 'email']);
```

### Via Script
```bash
php get_etudiants.php
```

### Via API (après connexion admin)
```
GET /api/v1/admin/users?role=etudiant
```

---

## 📝 Notes Importantes

1. **Mots de passe** : Tous les étudiants générés ont le mot de passe `password`
2. **Emails** : Les emails sont générés automatiquement (format `example.org`, `example.net`, etc.)
3. **Données réalistes** : Les noms sont générés par Faker, donc ils peuvent sembler étranges
4. **Emprunts** : Les emprunts existants ont déjà des QR codes générés (si créés après l'implémentation)

---

## ✅ Comptes de Test Rapides

### Pour tester rapidement, utilisez ces 3 comptes :

1. **Loy Bailey** (IL)
   - Email : `hamill.abbie@example.com`
   - Mot de passe : `password`
   - Parfait pour : Nouvelles réservations

2. **Max Heller** (IL)
   - Email : `jcassin@example.org`
   - Mot de passe : `password`
   - Parfait pour : Voir plusieurs emprunts

3. **Giuseppe Waelchi** (IL)
   - Email : `farrell.valentina@example.org`
   - Mot de passe : `password`
   - Parfait pour : Tester les réclamations

---

## 🚀 Commandes Utiles

### Voir tous les étudiants
```bash
php get_etudiants.php
```

### Voir un étudiant spécifique
```bash
php artisan tinker
>>> App\Models\User::where('email', 'hamill.abbie@example.com')->first();
```

### Voir les emprunts d'un étudiant
```bash
php artisan tinker
>>> $etudiant = App\Models\User::where('email', 'hamill.abbie@example.com')->first();
>>> $etudiant->emprunts;
```

---

**Dernière mise à jour** : Après remplissage de la base de données

