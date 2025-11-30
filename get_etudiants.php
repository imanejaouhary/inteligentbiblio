<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n=== COMPTES ETUDIANTS POUR TEST ===\n\n";

$etudiants = \App\Models\User::where('role', 'etudiant')
    ->orderBy('id')
    ->take(15)
    ->get(['id', 'name', 'email', 'filiere']);

foreach ($etudiants as $index => $etudiant) {
    echo ($index + 1) . ". " . $etudiant->name . "\n";
    echo "   Email: " . $etudiant->email . "\n";
    echo "   Filiere: " . $etudiant->filiere . "\n";
    echo "   ID: " . $etudiant->id . "\n";
    
    // Statistiques
    $emprunts = \App\Models\Emprunt::where('etudiant_id', $etudiant->id)->count();
    $reclamations = \App\Models\Reclamation::where('etudiant_id', $etudiant->id)->count();
    echo "   Emprunts: " . $emprunts . "\n";
    echo "   Reclamations: " . $reclamations . "\n";
    echo "\n";
}

echo "Mot de passe par defaut: password\n";
echo "\n=== REPARTITION PAR FILIERE ===\n";
echo "IL: " . \App\Models\User::where('role', 'etudiant')->where('filiere', 'IL')->count() . " etudiants\n";
echo "ADIA: " . \App\Models\User::where('role', 'etudiant')->where('filiere', 'ADIA')->count() . " etudiants\n";

