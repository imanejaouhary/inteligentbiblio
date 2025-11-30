<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Emprunt;
use App\Models\Reclamation;
use App\Models\Livre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;

class BibliothecaireController extends Controller
{
    public function emprunts(Request $request): JsonResponse
    {
        $emprunts = Emprunt::with(['etudiant', 'livre'])
            ->orderByDesc('date_emprunt')
            ->paginate($request->integer('per_page', 20));

        return response()->json($emprunts);
    }

    public function reclamations(Request $request): JsonResponse
    {
        $reclamations = Reclamation::with(['etudiant', 'bibliothecaire'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($reclamations);
    }

    /**
     * Répondre à une réclamation
     */
    public function repondreReclamation(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reponse' => ['required', 'string'],
            'statut' => ['sometimes', 'in:en_attente,en_cours,resolu'],
        ]);

        $reclamation = Reclamation::findOrFail($id);

        $reclamation->update([
            'reponse' => $validated['reponse'],
            'statut' => $validated['statut'] ?? $reclamation->statut,
            'biblio_id' => $request->user()->id,
            'repondu_at' => now(),
        ]);

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'repondre-reclamation',
            'target_type' => 'reclamation',
            'target_id' => $reclamation->id,
            'metadata' => [
                'etudiant_id' => $reclamation->etudiant_id,
                'statut' => $reclamation->statut,
            ],
        ]);

        return response()->json([
            'message' => 'Réponse ajoutée avec succès.',
            'data' => $reclamation->load(['etudiant', 'bibliothecaire']),
        ]);
    }

    /**
     * Mettre à jour le statut d'une réclamation
     */
    public function updateStatutReclamation(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'statut' => ['required', 'in:en_attente,en_cours,resolu'],
        ]);

        $reclamation = Reclamation::findOrFail($id);

        $reclamation->update([
            'statut' => $validated['statut'],
        ]);

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'data' => $reclamation->load(['etudiant', 'bibliothecaire']),
        ]);
    }

    public function validerRetour(Request $request, int $id): JsonResponse
    {
        /** @var Emprunt $emprunt */
        $emprunt = Emprunt::with('livre')->findOrFail($id);

        if ($emprunt->statut !== Emprunt::STATUT_EN_ATTENTE_RETOUR) {
            return response()->json([
                'message' => 'Cet emprunt n\'est pas en attente de retour.',
                'errors' => [
                    'emprunt' => ['Statut invalide pour validation de retour.'],
                ],
            ], 422);
        }

        DB::transaction(function () use ($emprunt, $request): void {
            /** @var Livre $livre */
            $livre = $emprunt->livre()->lockForUpdate()->first();

            $livre->increment('quantite');

            $emprunt->update([
                'statut' => Emprunt::STATUT_RETOURNE,
                'date_retour_effective' => CarbonImmutable::today(),
            ]);

            AuditLog::create([
                'admin_id' => $request->user()->id,
                'action' => 'valider-retour',
                'target_type' => 'emprunt',
                'target_id' => $emprunt->id,
                'metadata' => [
                    'livre_id' => $livre->id,
                    'etudiant_id' => $emprunt->etudiant_id,
                ],
            ]);
        });

        return response()->json([
            'message' => 'Retour validé avec succès.',
            'data' => $emprunt->fresh('livre'),
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $stats = Cache::remember('biblio_stats_detailed', 300, function () {
            // Statistiques de base
            $baseStats = [
                'total_livres' => Livre::count(),
                'total_emprunts' => Emprunt::count(),
                'emprunts_en_cours' => Emprunt::where('statut', Emprunt::STATUT_EN_COURS)->count(),
                'emprunts_retard' => Emprunt::where('statut', Emprunt::STATUT_RETARD)->count(),
                'emprunts_retournes' => Emprunt::where('statut', Emprunt::STATUT_RETOURNE)->count(),
                'reclamations_en_attente' => Reclamation::where('statut', Reclamation::STATUT_EN_ATTENTE)->count(),
                'reclamations_en_cours' => Reclamation::where('statut', Reclamation::STATUT_EN_COURS)->count(),
                'reclamations_resolues' => Reclamation::where('statut', Reclamation::STATUT_RESOLU)->count(),
            ];

            // Emprunts par statut (graphique)
            $empruntsParStatut = Emprunt::selectRaw('statut, COUNT(*) as total')
                ->groupBy('statut')
                ->get()
                ->map(fn($item) => [
                    'statut' => $item->statut,
                    'total' => $item->total,
                ]);

            // Réclamations par statut (graphique)
            $reclamationsParStatut = Reclamation::selectRaw('statut, COUNT(*) as total')
                ->groupBy('statut')
                ->get()
                ->map(fn($item) => [
                    'statut' => $item->statut,
                    'total' => $item->total,
                ]);

            // Emprunts des 7 derniers jours
            $emprunts7Jours = Emprunt::selectRaw('DATE(date_emprunt) as date, COUNT(*) as total')
                ->where('date_emprunt', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'total' => $item->total,
                ]);

            // Top 5 livres les plus empruntés
            $topLivres = Livre::withCount('emprunts')
                ->orderByDesc('emprunts_count')
                ->limit(5)
                ->get()
                ->map(fn($livre) => [
                    'titre' => $livre->titre,
                    'total_emprunts' => $livre->emprunts_count,
                ]);

            return array_merge($baseStats, [
                'graphiques' => [
                    'emprunts_par_statut' => $empruntsParStatut,
                    'reclamations_par_statut' => $reclamationsParStatut,
                    'emprunts_7_jours' => $emprunts7Jours,
                    'top_livres' => $topLivres,
                ],
            ]);
        });

        return response()->json([
            'message' => 'Statistiques récupérées.',
            'data' => $stats,
        ]);
    }

    /**
     * Scanner un QR code pour valider une réservation
     */
    public function scanQrReservation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_data' => ['required', 'string'],
        ]);

        try {
            $qrData = json_decode($validated['qr_data'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return response()->json([
                'message' => 'QR code invalide.',
                'errors' => [
                    'qr_data' => ['Le format du QR code est invalide.'],
                ],
            ], 422);
        }

        // Vérifier que c'est bien un QR code de réservation
        if (!isset($qrData['type']) || $qrData['type'] !== 'reservation') {
            return response()->json([
                'message' => 'Type de QR code invalide.',
                'errors' => [
                    'qr_data' => ['Ce QR code n\'est pas une réservation.'],
                ],
            ], 422);
        }

        // Récupérer l'emprunt
        $emprunt = Emprunt::with(['etudiant', 'livre'])
            ->where('id', $qrData['emprunt_id'])
            ->firstOrFail();

        // Vérifier le token
        if (!$emprunt->reservation_token || 
            $emprunt->reservation_token !== hash('sha256', $qrData['token'])) {
            return response()->json([
                'message' => 'Token invalide.',
                'errors' => [
                    'qr_data' => ['Le token du QR code ne correspond pas.'],
                ],
            ], 403);
        }

        // Vérifier que l'emprunt est en cours (pas déjà retourné)
        if ($emprunt->statut === Emprunt::STATUT_RETOURNE) {
            return response()->json([
                'message' => 'Cet emprunt a déjà été retourné.',
                'errors' => [
                    'emprunt' => ['L\'emprunt est déjà retourné.'],
                ],
            ], 422);
        }

        // Logger la validation
        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'scan-qr-reservation',
            'target_type' => 'emprunt',
            'target_id' => $emprunt->id,
            'metadata' => [
                'livre_id' => $emprunt->livre_id,
                'etudiant_id' => $emprunt->etudiant_id,
                'scanned_at' => now()->toIso8601String(),
            ],
        ]);

        return response()->json([
            'message' => 'QR code validé avec succès.',
            'data' => [
                'emprunt' => $emprunt,
                'valide' => true,
                'peut_valider' => $emprunt->statut === Emprunt::STATUT_EN_COURS || 
                                $emprunt->statut === Emprunt::STATUT_RETARD,
            ],
        ]);
    }

    /**
     * Scanner un QR code pour valider un retour
     */
    public function scanQrRetour(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_data' => ['required', 'string'],
        ]);

        try {
            $qrData = json_decode($validated['qr_data'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return response()->json([
                'message' => 'QR code invalide.',
                'errors' => [
                    'qr_data' => ['Le format du QR code est invalide.'],
                ],
            ], 422);
        }

        // Récupérer l'emprunt
        $emprunt = Emprunt::with(['etudiant', 'livre'])
            ->where('id', $qrData['emprunt_id'])
            ->firstOrFail();

        // Vérifier le token
        if (!$emprunt->reservation_token || 
            $emprunt->reservation_token !== hash('sha256', $qrData['token'])) {
            return response()->json([
                'message' => 'Token invalide.',
                'errors' => [
                    'qr_data' => ['Le token du QR code ne correspond pas.'],
                ],
            ], 403);
        }

        // Vérifier que l'emprunt peut être retourné
        if (!in_array($emprunt->statut, [Emprunt::STATUT_EN_COURS, Emprunt::STATUT_RETARD, Emprunt::STATUT_EN_ATTENTE_RETOUR])) {
            return response()->json([
                'message' => 'Cet emprunt ne peut pas être retourné.',
                'errors' => [
                    'emprunt' => ['L\'emprunt a déjà été retourné ou n\'est pas valide.'],
                ],
            ], 422);
        }

        // Valider le retour
        DB::transaction(function () use ($emprunt, $request): void {
            /** @var Livre $livre */
            $livre = $emprunt->livre()->lockForUpdate()->first();

            $livre->increment('quantite');

            $emprunt->update([
                'statut' => Emprunt::STATUT_RETOURNE,
                'date_retour_effective' => CarbonImmutable::today(),
            ]);

            AuditLog::create([
                'admin_id' => $request->user()->id,
                'action' => 'scan-qr-retour',
                'target_type' => 'emprunt',
                'target_id' => $emprunt->id,
                'metadata' => [
                    'livre_id' => $livre->id,
                    'etudiant_id' => $emprunt->etudiant_id,
                    'scanned_at' => now()->toIso8601String(),
                ],
            ]);
        });

        return response()->json([
            'message' => 'Retour validé avec succès via QR code.',
            'data' => $emprunt->fresh(['livre', 'etudiant']),
        ]);
    }
}



