@echo off
echo ========================================
echo   REMPLISSAGE DE LA BASE DE DONNEES
echo ========================================
echo.
echo Cette commande va :
echo - Supprimer toutes les tables existantes
echo - Recréer les tables
echo - Remplir avec des donnees de test
echo.
echo ATTENTION : Toutes les donnees existantes seront supprimees !
echo.
pause

echo.
echo Execution des migrations et seeders...
php artisan migrate:fresh --seed

echo.
echo ========================================
echo   TERMINE !
echo ========================================
echo.
echo Comptes de test disponibles :
echo - Admin: admin@ecole.test / admin1234
echo - Bibliothecaire: biblio@ecole.test / biblio1234
echo - Professeur: prof@ecole.test / prof1234
echo.
pause

