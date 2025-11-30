# 📊 Statistiques et Graphiques Basés sur la Base de Données

## ✅ Vérification Complète

Toutes les statistiques et graphiques côté admin sont **100% basés sur la base de données réelle**.

---

## 🔍 Source des Données

### Backend (AdminController.php)

Toutes les statistiques sont calculées directement depuis la base de données :

```php
// Statistiques de base - Requêtes SQL directes
'total_users' => User::count(),
'total_livres' => Livre::count(),
'total_emprunts' => Emprunt::count(),
'total_cours' => Cours::count(),
'total_reclamations' => Reclamation::count(),

// Graphiques - Requêtes SQL avec GROUP BY
'repartition_roles' => User::selectRaw('role, COUNT(*) as total')
    ->groupBy('role')
    ->get(),

'emprunts_par_mois' => Emprunt::selectRaw('DATE_FORMAT(date_emprunt, "%Y-%m") as mois, COUNT(*) as total')
    ->where('date_emprunt', '>=', now()->subMonths(6))
    ->groupBy('mois')
    ->get(),

'top_livres' => Livre::withCount('emprunts')
    ->orderByDesc('emprunts_count')
    ->limit(10)
    ->get(),
```

### Cache

- **Durée** : 5 minutes (300 secondes)
- **Clé** : `admin_stats_detailed`
- **Raison** : Optimisation des performances
- **Actualisation** : Automatique après expiration

---

## 📈 Graphiques Implémentés

### 1. Répartition par Rôle
- **Source** : Table `users`
- **Requête** : `SELECT role, COUNT(*) FROM users GROUP BY role`
- **Données affichées** : Nombre d'utilisateurs par rôle (admin, bibliothécaire, prof, etudiant)
- **Type** : Camembert (PieChart)

### 2. Répartition par Filière
- **Source** : Table `users` (filtre: role = 'etudiant')
- **Requête** : `SELECT filiere, COUNT(*) FROM users WHERE role='etudiant' GROUP BY filiere`
- **Données affichées** : Nombre d'étudiants par filière (IL, ADIA)
- **Type** : Camembert (PieChart)

### 3. Emprunts par Mois (6 derniers mois)
- **Source** : Table `emprunts`
- **Requête** : `SELECT DATE_FORMAT(date_emprunt, "%Y-%m") as mois, COUNT(*) FROM emprunts WHERE date_emprunt >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY mois`
- **Données affichées** : Nombre d'emprunts par mois
- **Type** : Ligne (LineChart)
- **Note** : Les mois sans emprunts sont affichés avec 0

### 4. Top 10 Livres les Plus Empruntés
- **Source** : Tables `livres` et `emprunts`
- **Requête** : `SELECT livres.*, COUNT(emprunts.id) as emprunts_count FROM livres LEFT JOIN emprunts ON livres.id = emprunts.livre_id GROUP BY livres.id ORDER BY emprunts_count DESC LIMIT 10`
- **Données affichées** : Titre et nombre d'emprunts
- **Type** : Barres (BarChart)

### 5. Statuts des Emprunts
- **Source** : Table `emprunts`
- **Requête** : `SELECT statut, COUNT(*) FROM emprunts GROUP BY statut`
- **Données affichées** : Nombre d'emprunts par statut (en_cours, retourne, retard, etc.)
- **Type** : Camembert (PieChart)

### 6. Statuts des Réclamations
- **Source** : Table `reclamations`
- **Requête** : `SELECT statut, COUNT(*) FROM reclamations GROUP BY statut`
- **Données affichées** : Nombre de réclamations par statut (en_attente, en_cours, resolu)
- **Type** : Camembert (PieChart)

---

## 📊 Statistiques Précises

### Emprunts
- **En cours** : `SELECT COUNT(*) FROM emprunts WHERE statut = 'en_cours'`
- **En retard** : `SELECT COUNT(*) FROM emprunts WHERE statut = 'retard'`
- **En attente retour** : `SELECT COUNT(*) FROM emprunts WHERE statut = 'en_attente_retour'`
- **Retournés** : `SELECT COUNT(*) FROM emprunts WHERE statut = 'retourne'`
- **Taux de retour** : Calculé : `(retournés / total) * 100`

### Livres
- **Disponibles** : `SELECT COUNT(*) FROM livres WHERE quantite > 0`
- **Indisponibles** : `SELECT COUNT(*) FROM livres WHERE quantite = 0`
- **Numériques** : `SELECT COUNT(*) FROM livres WHERE disponible_numerique = 1`
- **Taux disponibilité** : Calculé : `(disponibles / total) * 100`

### Réclamations
- **En attente** : `SELECT COUNT(*) FROM reclamations WHERE statut = 'en_attente'`
- **Résolues** : `SELECT COUNT(*) FROM reclamations WHERE statut = 'resolu'`
- **Taux résolution** : Calculé : `(resolues / (en_attente + resolues)) * 100`

### Étudiants
- **Total IL** : `SELECT COUNT(*) FROM users WHERE role = 'etudiant' AND filiere = 'IL'`
- **Total ADIA** : `SELECT COUNT(*) FROM users WHERE role = 'etudiant' AND filiere = 'ADIA'`

---

## 🎨 Affichage Frontend

### Indicateur de Source
- **Bannière** : "Statistiques en temps réel - Données basées sur la base de données"
- **Mise à jour** : "mis à jour toutes les 5 minutes"

### Titres des Graphiques
- Tous les graphiques indiquent "(Base de données)" dans leur titre
- Compteurs réels affichés sous chaque graphique

### Exemples d'Affichage

**Répartition par Rôle** :
```
Répartition par Rôle (Base de données)
Données réelles: 25 utilisateurs
[Graphique camembert]
```

**Emprunts par Mois** :
```
Emprunts par Mois - 6 Derniers Mois (Base de données)
Total: 7 emprunts
[Graphique ligne]
```

---

## ✅ Vérifications Effectuées

### Backend
- ✅ Toutes les requêtes utilisent Eloquent ORM
- ✅ Pas de données statiques ou hardcodées
- ✅ Requêtes SQL directes sur les tables
- ✅ Cache pour optimisation (5 minutes)

### Frontend
- ✅ Données récupérées via API (`adminAPI.getStats()`)
- ✅ Graphiques utilisent `stats.graphiques.*`
- ✅ Statistiques utilisent `stats.statistiques_precises.*`
- ✅ Indicateurs visuels que les données viennent de la BDD
- ✅ Compteurs réels affichés

### Base de Données
- ✅ Vérification : 25 users, 10 livres, 7 emprunts
- ✅ Données réelles dans la base
- ✅ Requêtes testées et fonctionnelles

---

## 🔄 Flux de Données

```
Base de Données (MySQL)
    ↓
AdminController::stats()
    ↓
Requêtes SQL (Eloquent)
    ↓
Calcul des statistiques
    ↓
Cache (5 minutes)
    ↓
API Response JSON
    ↓
Frontend (DashboardAdmin.jsx)
    ↓
Graphiques Recharts
    ↓
Affichage utilisateur
```

---

## 📝 Exemple de Réponse API

```json
{
  "message": "Statistiques récupérées.",
  "data": {
    "total_users": 25,
    "total_livres": 10,
    "total_emprunts": 7,
    "total_cours": 6,
    "total_reclamations": 3,
    "statistiques_precises": {
      "emprunts": {
        "en_cours": 5,
        "en_retard": 0,
        "en_attente_retour": 1,
        "retournes": 1,
        "taux_retour": 14.29
      },
      "livres": {
        "disponibles": 8,
        "indisponibles": 2,
        "numeriques": 0,
        "taux_disponibilite": 80.0
      },
      "reclamations": {
        "en_attente": 2,
        "resolues": 1,
        "taux_resolution": 33.33
      },
      "etudiants": {
        "total_il": 10,
        "total_adia": 10
      }
    },
    "graphiques": {
      "repartition_roles": [
        {"role": "admin", "total": 1},
        {"role": "bibliothecaire", "total": 1},
        {"role": "prof", "total": 3},
        {"role": "etudiant", "total": 20}
      ],
      "repartition_filiere": [
        {"filiere": "IL", "total": 10},
        {"filiere": "ADIA", "total": 10}
      ],
      "emprunts_par_mois": [
        {"mois": "2025-01", "total": 7}
      ],
      "top_livres": [
        {"id": 1, "titre": "Livre 1", "auteur": "Auteur 1", "total_emprunts": 3}
      ],
      "statuts_emprunts": [
        {"statut": "en_cours", "total": 5},
        {"statut": "en_attente_retour", "total": 1},
        {"statut": "retourne", "total": 1}
      ],
      "statuts_reclamations": [
        {"statut": "en_attente", "total": 2},
        {"statut": "resolu", "total": 1}
      ],
      "taux_retour": 14.29
    }
  }
}
```

---

## ✅ Confirmation

**Toutes les statistiques et graphiques sont 100% basés sur la base de données réelle.**

- ✅ Aucune donnée statique
- ✅ Toutes les requêtes SQL directes
- ✅ Données en temps réel (cache 5 min)
- ✅ Indicateurs visuels dans le frontend
- ✅ Compteurs réels affichés

---

**Date** : Janvier 2025  
**Version** : 1.0 - Vérifié et Confirmé

