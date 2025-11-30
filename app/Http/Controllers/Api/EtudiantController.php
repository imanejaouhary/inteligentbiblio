<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emprunt;
use App\Models\Evaluation;
use App\Models\Livre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EtudiantController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $key = 'etudiant_stats_detailed_' . $user->id;

        $stats = Cache::remember($key, 300, function () use ($user) {
            // Statistiques de base
            $totalEmprunts = Emprunt::where('etudiant_id', $user->id)->count();
            $enCours = Emprunt::where('etudiant_id', $user->id)
                ->where('statut', Emprunt::STATUT_EN_COURS)
                ->count();
            $retard = Emprunt::where('etudiant_id', $user->id)
                ->where('statut', Emprunt::STATUT_RETARD)
                ->count();
            $retournes = Emprunt::where('etudiant_id', $user->id)
                ->where('statut', Emprunt::STATUT_RETOURNE)
                ->count();
            $totalReclamations = \App\Models\Reclamation::where('etudiant_id', $user->id)->count();

            // Emprunts par statut (graphique)
            $empruntsParStatut = Emprunt::where('etudiant_id', $user->id)
                ->selectRaw('statut, COUNT(*) as total')
                ->groupBy('statut')
                ->get()
                ->map(fn($item) => [
                    'statut' => $item->statut,
                    'total' => $item->total,
                ]);

            // Historique des emprunts (6 derniers mois)
            $historiqueEmprunts = Emprunt::where('etudiant_id', $user->id)
                ->selectRaw('DATE_FORMAT(date_emprunt, "%Y-%m") as mois, COUNT(*) as total')
                ->where('date_emprunt', '>=', now()->subMonths(6))
                ->groupBy('mois')
                ->orderBy('mois')
                ->get()
                ->map(fn($item) => [
                    'mois' => $item->mois,
                    'total' => $item->total,
                ]);

            // Livres les plus empruntés par cet étudiant
            $livresFavoris = Livre::whereHas('emprunts', function ($query) use ($user) {
                    $query->where('etudiant_id', $user->id);
                })
                ->withCount(['emprunts' => function ($query) use ($user) {
                    $query->where('etudiant_id', $user->id);
                }])
                ->orderByDesc('emprunts_count')
                ->limit(5)
                ->get()
                ->map(fn($livre) => [
                    'titre' => $livre->titre,
                    'auteur' => $livre->auteur,
                    'total_emprunts' => $livre->emprunts_count,
                ]);

            return [
                'total_emprunts' => $totalEmprunts,
                'emprunts_en_cours' => $enCours,
                'emprunts_en_retard' => $retard,
                'emprunts_retournes' => $retournes,
                'total_reclamations' => $totalReclamations,
                'graphiques' => [
                    'emprunts_par_statut' => $empruntsParStatut,
                    'historique_emprunts' => $historiqueEmprunts,
                    'livres_favoris' => $livresFavoris,
                ],
            ];
        });

        return response()->json([
            'message' => 'Statistiques récupérées.',
            'data' => $stats,
        ]);
    }

    public function recommandations(Request $request): JsonResponse
    {
        $user = $request->user();

        // Livres déjà empruntés
        $empruntesIds = Emprunt::where('etudiant_id', $user->id)
            ->pluck('livre_id')
            ->unique()
            ->all();

        // 1) Auteurs déjà empruntés
        $auteurs = Livre::whereIn('id', $empruntesIds)
            ->pluck('auteur')
            ->unique()
            ->all();

        $recommandations = collect();

        if ($auteurs) {
            $parAuteurs = Livre::whereIn('auteur', $auteurs)
                ->whereNotIn('id', $empruntesIds)
                ->limit(6)
                ->get();

            $recommandations = $recommandations->merge($parAuteurs);
        }

        if ($recommandations->count() < 6) {
            $restant = 6 - $recommandations->count();

            // Livres les mieux notés non empruntés
            $topNotes = Evaluation::selectRaw('livre_id, AVG(note) as moyenne')
                ->whereNotIn('livre_id', $empruntesIds)
                ->groupBy('livre_id')
                ->orderByDesc('moyenne')
                ->limit($restant)
                ->pluck('livre_id')
                ->all();

            if ($topNotes) {
                $livresTop = Livre::whereIn('id', $topNotes)->get();
                $recommandations = $recommandations->merge($livresTop);
            }
        }

        $recommandations = $recommandations
            ->unique('id')
            ->values()
            ->take(6);

        return response()->json([
            'message' => 'Recommandations générées.',
            'data' => $recommandations,
        ]);
    }
}







