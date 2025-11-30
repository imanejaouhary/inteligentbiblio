#!/bin/bash

echo "========================================"
echo "  REMPLISSAGE DE LA BASE DE DONNEES"
echo "========================================"
echo ""
echo "Cette commande va :"
echo "- Supprimer toutes les tables existantes"
echo "- Recréer les tables"
echo "- Remplir avec des données de test"
echo ""
echo "ATTENTION : Toutes les données existantes seront supprimées !"
echo ""
read -p "Appuyez sur Entrée pour continuer..."

echo ""
echo "Exécution des migrations et seeders..."
php artisan migrate:fresh --seed

echo ""
echo "========================================"
echo "  TERMINÉ !"
echo "========================================"
echo ""
echo "Comptes de test disponibles :"
echo "- Admin: admin@ecole.test / admin1234"
echo "- Bibliothécaire: biblio@ecole.test / biblio1234"
echo "- Professeur: prof@ecole.test / prof1234"
echo ""

