<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function indexUsers(Request $request): JsonResponse
    {
        $query = User::query();

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->paginate($request->integer('per_page', 20));

        return response()->json($users);
    }

    public function storeUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,bibliothecaire,prof,etudiant'],
            'filiere' => ['required_if:role,etudiant', 'nullable', 'in:IL,ADIA'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'filiere' => $validated['filiere'] ?? null,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'data' => $user,
        ], 201);
    }

    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin' && $request->user()->id !== $user->id) {
            return response()->json([
                'message' => 'Impossible de modifier un autre administrateur.',
                'errors' => [
                    'user' => ['Vous ne pouvez pas modifier un autre utilisateur administrateur.'],
                ],
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', 'in:admin,bibliothecaire,prof,etudiant'],
            'filiere' => ['required_if:role,etudiant', 'nullable', 'in:IL,ADIA'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur modifié avec succès.',
            'data' => $user->fresh(),
        ]);
    }

    public function destroyUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Impossible de supprimer un autre administrateur.',
                'errors' => [
                    'user' => ['Vous ne pouvez pas supprimer un utilisateur administrateur.'],
                ],
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès.',
            'data' => null,
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $stats = Cache::remember('admin_stats_detailed', 300, function () {
            // Statistiques de base
            $baseStats = [
                'total_users' => User::count(),
                'total_livres' => Livre::count(),
                'total_emprunts' => Emprunt::count(),
                'total_cours' => \App\Models\Cours::count(),
                'total_reclamations' => \App\Models\Reclamation::count(),
            ];

            // Répartition par rôle
            $repartitionRoles = User::selectRaw('role, COUNT(*) as total')
                ->groupBy('role')
                ->get()
                ->map(fn($item) => [
                    'role' => $item->role,
                    'total' => $item->total,
                ]);

            // Répartition par filière (étudiants)
            $repartitionFiliere = User::where('role', 'etudiant')
                ->selectRaw('filiere, COUNT(*) as total')
                ->groupBy('filiere')
                ->get()
                ->map(fn($item) => [
                    'filiere' => $item->filiere,
                    'total' => $item->total,
                ]);

            // Emprunts par mois (6 derniers mois) - Basé sur la base de données
            $empruntsParMois = Emprunt::selectRaw('DATE_FORMAT(date_emprunt, "%Y-%m") as mois, COUNT(*) as total')
                ->where('date_emprunt', '>=', now()->subMonths(6))
                ->groupBy('mois')
                ->orderBy('mois')
                ->get()
                ->map(fn($item) => [
                    'mois' => $item->mois,
                    'total' => (int)$item->total,
                ]);

            // Si aucun emprunt dans les 6 derniers mois, ajouter les mois vides pour le graphique
            $moisVides = [];
            for ($i = 5; $i >= 0; $i--) {
                $mois = now()->subMonths($i)->format('Y-m');
                $existe = $empruntsParMois->firstWhere('mois', $mois);
                if (!$existe) {
                    $moisVides[] = ['mois' => $mois, 'total' => 0];
                }
            }
            $empruntsParMois = $empruntsParMois->merge($moisVides)->sortBy('mois')->values();

            // Top 10 livres les plus empruntés
            $topLivres = Livre::withCount('emprunts')
                ->orderByDesc('emprunts_count')
                ->limit(10)
                ->get()
                ->map(fn($livre) => [
                    'id' => $livre->id,
                    'titre' => $livre->titre,
                    'auteur' => $livre->auteur,
                    'total_emprunts' => $livre->emprunts_count,
                ]);

            // Statuts des emprunts
            $statutsEmprunts = Emprunt::selectRaw('statut, COUNT(*) as total')
                ->groupBy('statut')
                ->get()
                ->map(fn($item) => [
                    'statut' => $item->statut,
                    'total' => $item->total,
                ]);

            // Statuts des réclamations
            $statutsReclamations = \App\Models\Reclamation::selectRaw('statut, COUNT(*) as total')
                ->groupBy('statut')
                ->get()
                ->map(fn($item) => [
                    'statut' => $item->statut,
                    'total' => $item->total,
                ]);

            // Taux de retour
            $totalRetournes = Emprunt::where('statut', Emprunt::STATUT_RETOURNE)->count();
            $totalEmprunts = Emprunt::count();
            $tauxRetour = $totalEmprunts > 0 
                ? round(($totalRetournes / $totalEmprunts) * 100, 2)
                : 0;

            // Statistiques précises supplémentaires
            $empruntsEnCours = Emprunt::where('statut', Emprunt::STATUT_EN_COURS)->count();
            $empruntsEnRetard = Emprunt::where('statut', Emprunt::STATUT_RETARD)->count();
            $empruntsEnAttenteRetour = Emprunt::where('statut', Emprunt::STATUT_EN_ATTENTE_RETOUR)->count();
            
            $livresDisponibles = Livre::where('quantite', '>', 0)->count();
            $livresIndisponibles = Livre::where('quantite', '=', 0)->count();
            $livresNumeriques = Livre::where('disponible_numerique', true)->count();
            
            $reclamationsEnAttente = \App\Models\Reclamation::where('statut', \App\Models\Reclamation::STATUT_EN_ATTENTE)->count();
            $reclamationsResolues = \App\Models\Reclamation::where('statut', \App\Models\Reclamation::STATUT_RESOLU)->count();
            
            $etudiantsParFiliere = User::where('role', 'etudiant')
                ->selectRaw('filiere, COUNT(*) as total')
                ->groupBy('filiere')
                ->get()
                ->pluck('total', 'filiere')
                ->toArray();

            return array_merge($baseStats, [
                'statistiques_precises' => [
                    'emprunts' => [
                        'en_cours' => $empruntsEnCours,
                        'en_retard' => $empruntsEnRetard,
                        'en_attente_retour' => $empruntsEnAttenteRetour,
                        'retournes' => $totalRetournes,
                        'taux_retour' => $tauxRetour,
                    ],
                    'livres' => [
                        'disponibles' => $livresDisponibles,
                        'indisponibles' => $livresIndisponibles,
                        'numeriques' => $livresNumeriques,
                        'taux_disponibilite' => $baseStats['total_livres'] > 0 
                            ? round(($livresDisponibles / $baseStats['total_livres']) * 100, 2)
                            : 0,
                    ],
                    'reclamations' => [
                        'en_attente' => $reclamationsEnAttente,
                        'resolues' => $reclamationsResolues,
                        'taux_resolution' => ($reclamationsEnAttente + $reclamationsResolues) > 0
                            ? round(($reclamationsResolues / ($reclamationsEnAttente + $reclamationsResolues)) * 100, 2)
                            : 0,
                    ],
                    'etudiants' => [
                        'total_il' => $etudiantsParFiliere['IL'] ?? 0,
                        'total_adia' => $etudiantsParFiliere['ADIA'] ?? 0,
                    ],
                ],
                'graphiques' => [
                    'repartition_roles' => $repartitionRoles,
                    'repartition_filiere' => $repartitionFiliere,
                    'emprunts_par_mois' => $empruntsParMois,
                    'top_livres' => $topLivres,
                    'statuts_emprunts' => $statutsEmprunts,
                    'statuts_reclamations' => $statutsReclamations,
                    'taux_retour' => $tauxRetour,
                ],
            ]);
        });

        return response()->json([
            'message' => 'Statistiques récupérées.',
            'data' => $stats,
        ]);
    }
}







