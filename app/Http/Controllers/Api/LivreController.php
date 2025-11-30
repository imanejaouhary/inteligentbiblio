<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Livre\StoreLivreRequest;
use App\Http\Requests\Livre\UpdateLivreRequest;
use App\Models\AuditLog;
use App\Models\Livre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LivreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $key = 'livres_index_' . md5($request->get('q') . '|' . $request->get('page', 1));

        $result = Cache::remember($key, 600, function () use ($request) {
            $query = Livre::query();

            if ($search = $request->get('q')) {
                $query->where(function ($q) use ($search): void {
                    $q->where('titre', 'like', "%{$search}%")
                        ->orWhere('auteur', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            }

            return $query->paginate($request->integer('per_page', 20));
        });

        return response()->json($result);
    }

    public function store(StoreLivreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Convertir disponible_numerique en boolean si présent
        if (isset($validated['disponible_numerique'])) {
            $validated['disponible_numerique'] = filter_var($validated['disponible_numerique'], FILTER_VALIDATE_BOOLEAN);
        }
        
        // Nettoyer image_path : convertir chaîne vide en null
        if (isset($validated['image_path']) && $validated['image_path'] === '') {
            $validated['image_path'] = null;
        }
        
        $livre = Livre::create($validated);

        // Log de l'action
        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'livre_create',
            'target_type' => 'livre',
            'target_id' => $livre->id,
            'metadata' => [
                'titre' => $livre->titre,
            ],
        ]);

        return response()->json([
            'message' => 'Livre créé avec succès.',
            'data' => $livre->fresh(),
            'id' => $livre->id, // Ajouter l'ID au niveau racine pour faciliter l'accès
        ], 201);
    }

    public function update(UpdateLivreRequest $request, int $id): JsonResponse
    {
        $livre = Livre::findOrFail($id);
        $validated = $request->validated();
        
        // Convertir disponible_numerique en boolean si présent
        if (isset($validated['disponible_numerique'])) {
            $validated['disponible_numerique'] = filter_var($validated['disponible_numerique'], FILTER_VALIDATE_BOOLEAN);
        }
        
        // Nettoyer image_path : convertir chaîne vide en null
        if (isset($validated['image_path']) && $validated['image_path'] === '') {
            $validated['image_path'] = null;
        }
        
        $livre->update($validated);

        // Log de l'action
        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'livre_update',
            'target_type' => 'livre',
            'target_id' => $livre->id,
            'metadata' => [
                'titre' => $livre->titre,
            ],
        ]);

        return response()->json([
            'message' => 'Livre mis à jour avec succès.',
            'data' => $livre->fresh(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $livre = Livre::findOrFail($id);

        DB::transaction(function () use ($livre, $request): void {
            if ($livre->image_path) {
                Storage::disk('public')->delete($livre->image_path);
            }

            $livreId = $livre->id;
            $livre->emprunts()->delete();
            $livre->evaluations()->delete();

            $livre->delete();

            AuditLog::create([
                'admin_id' => $request->user()->id,
                'action' => 'livre_delete',
                'target_type' => 'livre',
                'target_id' => $livreId,
                'metadata' => [
                    'reason' => 'Livre supprimé par admin',
                ],
            ]);
        });

        return response()->json([
            'message' => 'Livre supprimé avec succès.',
            'data' => null,
        ]);
    }

    /**
     * Télécharger un livre numérique
     */
    public function download(Request $request, int $id): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $livre = Livre::findOrFail($id);

        // Vérifier que le livre est disponible en numérique
        if (!$livre->disponible_numerique || !$livre->fichier_path) {
            return response()->json([
                'message' => 'Ce livre n\'est pas disponible en version numérique.',
                'errors' => [
                    'livre' => ['Version numérique non disponible.'],
                ],
            ], 404);
        }

        // Télécharger le fichier avec le nom du livre (sécurisé)
        $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $livre->titre) . '.' . ($livre->format ?? 'pdf');
        
        // Vérifier les permissions selon le rôle
        if ($user->role === 'admin' || $user->role === 'bibliothecaire') {
            // Admin et bibliothécaire peuvent toujours télécharger
            return Storage::disk('private')->download(
                $livre->fichier_path,
                $filename
            );
        }

        if ($user->role === 'etudiant') {
            // Pour les livres numériques, permettre le téléchargement SANS emprunt actif
            // L'étudiant peut télécharger le PDF directement, indépendamment de l'emprunt physique
            // C'est logique : un livre numérique peut être téléchargé même sans emprunt physique
            
            // Optionnel : Logger si l'étudiant a un emprunt actif (pour statistiques uniquement)
            $empruntActif = Emprunt::where('etudiant_id', $user->id)
                ->where('livre_id', $livre->id)
                ->whereIn('statut', [
                    Emprunt::STATUT_EN_COURS, 
                    Emprunt::STATUT_RETARD,
                    Emprunt::STATUT_EN_ATTENTE_RETOUR
                ])
                ->first();
            
            // Le téléchargement est AUTORISÉ même sans emprunt actif
            // Car le livre numérique peut être téléchargé indépendamment de l'emprunt physique

            // Logger le téléchargement
            try {
                AuditLog::create([
                    'admin_id' => $user->id,
                    'action' => 'download-livre',
                    'target_type' => 'livre',
                    'target_id' => $livre->id,
                    'metadata' => [
                        'livre_titre' => $livre->titre,
                        'format' => $livre->format,
                        'emprunt_id' => $empruntActif?->id,
                        'statut_emprunt' => $empruntActif?->statut ?? 'aucun',
                        'type' => 'telechargement_numerique',
                    ],
                ]);
            } catch (\Exception $e) {
                \Log::warning("Erreur lors de la création de l'audit log: " . $e->getMessage());
            }
            
            \Log::info("Téléchargement PDF autorisé - Étudiant {$user->id}, Livre {$livre->id}, Emprunt actif: " . ($empruntActif?->id ?? 'aucun'));

            return Storage::disk('private')->download(
                $livre->fichier_path,
                $filename
            );
        }

        return response()->json([
            'message' => 'Forbidden.',
            'errors' => [
                'role' => ['Rôle non autorisé pour ce téléchargement.'],
            ],
        ], 403);
    }

    /**
     * Upload un fichier numérique pour un livre (Admin uniquement)
     */
    public function uploadFile(Request $request, int $id): JsonResponse
    {
        $livre = Livre::findOrFail($id);

        $validated = $request->validate([
            'fichier' => [
                'required',
                'file',
                'mimes:pdf,epub,mobi',
                'max:100', // 100MB
            ],
        ]);

        $file = $validated['fichier'];
        $path = $file->store('livres', 'private');

        $livre->update([
            'disponible_numerique' => true,
            'fichier_path' => $path,
            'format' => $file->getClientOriginalExtension(),
            'taille_fichier' => $file->getSize(),
        ]);

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'upload-livre-numerique',
            'target_type' => 'livre',
            'target_id' => $livre->id,
            'metadata' => [
                'format' => $file->getClientOriginalExtension(),
                'taille' => $file->getSize(),
            ],
        ]);

        return response()->json([
            'message' => 'Fichier uploadé avec succès.',
            'data' => $livre->fresh(),
        ], 201);
    }
}







