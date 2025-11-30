@echo off
echo ========================================
echo   PREPARATION COMPLETE DU PROJET
echo ========================================
echo.
echo Cette commande va :
echo 1. Nettoyer la base de donnees
echo 2. Executer les migrations
echo 3. Ajouter des donnees realistes pour test
echo 4. Creer les dossiers necessaires
echo 5. Creer le lien symbolique storage
echo.
echo ATTENTION : Toutes les donnees existantes seront supprimees !
echo.
pause

echo.
echo Etape 1: Nettoyage de la base de donnees...
php artisan migrate:fresh

echo.
echo Etape 2: Ajout de donnees realistes...
php artisan db:seed --class=RealisticDataSeeder

echo.
echo Etape 3: Creation des dossiers...
if not exist "storage\app\public\qr_codes" mkdir "storage\app\public\qr_codes"
if not exist "storage\app\private\livres" mkdir "storage\app\private\livres"
if not exist "storage\app\private\cours" mkdir "storage\app\private\cours"

echo.
echo Etape 4: Creation du lien symbolique...
php artisan storage:link

echo.
echo ========================================
echo   TERMINE !
echo ========================================
echo.
echo Le projet est maintenant completement configure avec des donnees realistes.
echo.
echo Comptes de test disponibles :
echo.
echo ADMIN:
echo   Email: admin@universite.ma
echo   Password: admin1234
echo.
echo BIBLIOTHECAIRE:
echo   Email: biblio@universite.ma
echo   Password: biblio1234
echo.
echo PROFESSEURS:
echo   Email: y.idrissi@universite.ma
echo   Password: prof1234
echo.
echo ETUDIANTS (exemples):
echo   Email: ahmed.benali@universite.ma
echo   Password: etudiant1234
echo   Filiere: IL
echo.
echo   Email: hassan.bensaid@universite.ma
echo   Password: etudiant1234
echo   Filiere: ADIA
echo.
echo Donnees creees:
echo - 10 livres realistes
echo - 6 cours (3 IL, 3 ADIA)
echo - 7 emprunts (5 en cours avec QR codes, 2 retournes)
echo - Evaluations de livres
echo - Reclamations
echo.
echo Vous pouvez maintenant tester toutes les fonctionnalites !
echo.
pause

