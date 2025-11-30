<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cours;
use App\Models\CoursFiliere;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoursController extends Controller
{
    private const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB

    public function index(Request $request): JsonResponse
    {
        $query = Cours::with('prof', 'filieres');

        $cours = $query->paginate($request->integer('per_page', 20));

        return response()->json($cours);
    }

    public function mesCours(Request $request): JsonResponse
    {
        $user = $request->user();

        $cours = Cours::with('filieres')
            ->where('prof_id', $user->id)
            ->paginate($request->integer('per_page', 20));

        return response()->json($cours);
    }

    /**
     * Statistiques pour le professeur
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'prof') {
            return response()->json([
                'message' => 'Forbidden.',
                'errors' => [
                    'role' => ['Seuls les professeurs peuvent accéder à ces statistiques.'],
                ],
            ], 403);
        }

        $key = 'prof_stats_' . $user->id;

        $stats = Cache::remember($key, 300, function () use ($user) {
            // Statistiques de base
            $totalCours = Cours::where('prof_id', $user->id)->count();
            
            // Répartition par filière
            $coursIds = Cours::where('prof_id', $user->id)->pluck('id');
            $coursParFiliere = CoursFiliere::whereIn('cours_id', $coursIds)
                ->selectRaw('filiere, COUNT(*) as total')
                ->groupBy('filiere')
                ->get()
                ->map(fn($item) => [
                    'filiere' => $item->filiere,
                    'total' => $item->total,
                ]);

            // Cours publiés par mois (6 derniers mois)
            $coursParMois = Cours::where('prof_id', $user->id)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mois, COUNT(*) as total')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('mois')
                ->orderBy('mois')
                ->get()
                ->map(fn($item) => [
                    'mois' => $item->mois,
                    'total' => $item->total,
                ]);

            // Derniers cours publiés
            $derniersCours = Cours::where('prof_id', $user->id)
                ->with('filieres')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn($cours) => [
                    'id' => $cours->id,
                    'titre' => $cours->titre,
                    'description' => $cours->description,
                    'filieres' => $cours->filieres->pluck('filiere'),
                    'created_at' => $cours->created_at->toDateString(),
                ]);

            return [
                'total_cours' => $totalCours,
                'graphiques' => [
                    'cours_par_filiere' => $coursParFiliere,
                    'cours_par_mois' => $coursParMois,
                    'derniers_cours' => $derniersCours,
                ],
            ];
        });

        return response()->json([
            'message' => 'Statistiques récupérées.',
            'data' => $stats,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'prof') {
            return response()->json([
                'message' => 'Forbidden.',
                'errors' => [
                    'role' => ['Seuls les professeurs peuvent créer des cours.'],
                ],
            ], 403);
        }

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'filiere' => ['required', 'in:IL,ADIA'],
            'fichier' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx',
                'max:' . (self::MAX_FILE_SIZE / 1024),
            ],
        ]);

        $file = $validated['fichier'];

        $path = $file->store('cours', [
            'disk' => config('filesystems.default', 'local'),
        ]);

        $cours = Cours::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'prof_id' => $user->id,
            'fichier_path' => $path,
            'taille' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
        ]);

        CoursFiliere::create([
            'cours_id' => $cours->id,
            'filiere' => $validated['filiere'],
        ]);

        return response()->json([
            'message' => 'Cours créé avec succès.',
            'data' => $cours->load('filieres'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $cours = Cours::findOrFail($id);

        if ($cours->prof_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden.',
                'errors' => [
                    'cours' => ['Vous ne pouvez modifier que vos propres cours.'],
                ],
            ], 403);
        }

        $validated = $request->validate([
            'titre' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
        ]);

        $cours->update($validated);

        return response()->json([
            'message' => 'Cours mis à jour avec succès.',
            'data' => $cours,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $cours = Cours::findOrFail($id);

        if ($cours->prof_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden.',
                'errors' => [
                    'cours' => ['Vous ne pouvez supprimer que vos propres cours ou être admin.'],
                ],
            ], 403);
        }

        if ($cours->fichier_path) {
            Storage::disk(config('filesystems.default', 'local'))->delete($cours->fichier_path);
        }

        $cours->filieres()->delete();
        $cours->delete();

        return response()->json([
            'message' => 'Cours supprimé avec succès.',
            'data' => null,
        ]);
    }

    public function download(Request $request, int $id)
    {
        $user = $request->user();

        $cours = Cours::with('filieres')->findOrFail($id);

        if ($user->role === 'admin' || $user->role === 'prof' && $cours->prof_id === $user->id) {
            return Storage::disk(config('filesystems.default', 'local'))
                ->download($cours->fichier_path);
        }

        if ($user->role === 'etudiant') {
            $filiereAutorisee = $cours->filieres()
                ->where('filiere', $user->filiere)
                ->exists();

            if (!$filiereAutorisee) {
                return response()->json([
                    'message' => 'Accès refusé pour cette filière.',
                    'errors' => [
                        'filiere' => ['Vous n\'êtes pas autorisé à télécharger ce cours.'],
                    ],
                ], 403);
            }

            return Storage::disk(config('filesystems.default', 'local'))
                ->download($cours->fichier_path);
        }

        return response()->json([
            'message' => 'Forbidden.',
            'errors' => [
                'role' => ['Rôle non autorisé pour ce téléchargement.'],
            ],
        ], 403);
    }
}







