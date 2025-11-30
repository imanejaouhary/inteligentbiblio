@echo off
echo ========================================
echo   NETTOYAGE ET PREPARATION BASE DE DONNEES
echo ========================================
echo.
echo Cette commande va :
echo - Supprimer toutes les donnees de test
echo - Garder uniquement admin et bibliothecaire
echo - Ajouter des donnees minimales reelles
echo.
echo ATTENTION : Toutes les donnees de test seront supprimees !
echo.
pause

echo.
echo Nettoyage de la base de donnees...
php artisan db:seed --class=CleanDatabaseSeeder

echo.
echo Ajout de donnees minimales reelles...
php artisan db:seed --class=RealDataSeeder

echo.
echo ========================================
echo   TERMINE !
echo ========================================
echo.
echo La base de donnees est maintenant propre et prete pour de vraies donnees.
echo.
echo Comptes disponibles :
echo - Admin: admin@ecole.test / admin1234
echo - Bibliothecaire: biblio@ecole.test / biblio1234
echo.
echo Vous pouvez maintenant ajouter vos propres donnees via l'interface admin.
echo.
pause

