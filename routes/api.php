<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\LivreController;
use App\Http\Controllers\Api\CoursController;
use App\Http\Controllers\Api\EmpruntController;
use App\Http\Controllers\Api\ReclamationController;
use App\Http\Controllers\Api\EtudiantController;
use App\Http\Controllers\Api\BibliothecaireController;
use App\Http\Controllers\Api\SearchController;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Admin
        Route::middleware('role:admin')->group(function () {
            Route::get('admin/users', [AdminController::class, 'indexUsers']);
            Route::post('admin/users', [AdminController::class, 'storeUser']);
            Route::put('admin/users/{id}', [AdminController::class, 'updateUser']);
            Route::delete('admin/users/{id}', [AdminController::class, 'destroyUser']);
            Route::get('admin/stats', [AdminController::class, 'stats']);
        });

        // Livres
        Route::get('livres', [LivreController::class, 'index']);
        Route::get('livres/{id}/download', [LivreController::class, 'download']);
        Route::middleware('role:admin')->group(function () {
            Route::post('livres', [LivreController::class, 'store']);
            Route::put('livres/{id}', [LivreController::class, 'update']);
            Route::delete('livres/{id}', [LivreController::class, 'destroy']);
            Route::post('livres/{id}/upload-file', [LivreController::class, 'uploadFile']);
        });

        // Cours
        Route::get('cours', [CoursController::class, 'index']);
        Route::get('mes-cours', [CoursController::class, 'mesCours']);
        Route::get('cours/{id}/download', [CoursController::class, 'download']);
        Route::middleware('role:prof')->group(function () {
            Route::get('prof/stats', [CoursController::class, 'stats']);
            Route::post('cours', [CoursController::class, 'store']);
            Route::put('cours/{id}', [CoursController::class, 'update']);
            Route::delete('cours/{id}', [CoursController::class, 'destroy']);
        });

        // Emprunts & Bibliothécaire
        Route::get('emprunts', [EmpruntController::class, 'index']);
        Route::post('reserve', [EmpruntController::class, 'reserve']);
        Route::post('retour', [EmpruntController::class, 'retour']);
        Route::get('emprunts/{id}/qr-code', [EmpruntController::class, 'downloadQrCode']);
        Route::get('emprunts/{id}/qr-info', [EmpruntController::class, 'getQrCodeInfo']);
        Route::post('emprunts/{id}/regenerate-qr', [EmpruntController::class, 'regenerateQrCode']);

        Route::middleware('role:bibliothecaire')->group(function () {
            Route::get('biblio/emprunts', [BibliothecaireController::class, 'emprunts']);
            Route::get('biblio/reclamations', [BibliothecaireController::class, 'reclamations']);
            Route::post('biblio/reclamations/{id}/repondre', [BibliothecaireController::class, 'repondreReclamation']);
            Route::put('biblio/reclamations/{id}/statut', [BibliothecaireController::class, 'updateStatutReclamation']);
            Route::post('biblio/valider-retour/{id}', [BibliothecaireController::class, 'validerRetour']);
            Route::post('biblio/scan-qr-reservation', [BibliothecaireController::class, 'scanQrReservation']);
            Route::post('biblio/scan-qr-retour', [BibliothecaireController::class, 'scanQrRetour']);
            Route::get('biblio/stats', [BibliothecaireController::class, 'stats']);
        });

        // Réclamations
        Route::post('reclamations', [ReclamationController::class, 'store']);
        Route::get('reclamations', [ReclamationController::class, 'index']);

        // Recherche
        Route::get('search', [SearchController::class, 'search']);

        // Étudiant
        Route::get('etudiant/stats', [EtudiantController::class, 'stats']);
        Route::get('etudiant/recommandations', [EtudiantController::class, 'recommandations']);
    });
});


