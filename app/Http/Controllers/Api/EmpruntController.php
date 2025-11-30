<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emprunt;
use App\Models\Livre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;
use App\Services\QrCodeService;

class EmpruntController extends Controller
{
    private const DUREE_EMPRUNT_JOURS = 14;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Emprunt::with(['livre'])
            ->where('etudiant_id', $user->id)
            ->orderByDesc('date_emprunt');

        $emprunts = $query->paginate($request->integer('per_page', 20));

        return response()->json($emprunts);
    }

    public function reserve(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'etudiant') {
            return response()->json([
                'message' => 'Forbidden.',
                'errors' => [
                    'role' => ['Seuls les étudiants peuvent réserver un livre.'],
                ],
            ], 403);
        }

        $validated = $request->validate([
            'livre_id' => ['required', 'integer', 'exists:livres,id'],
        ]);

        $livre = Livre::lockForUpdate()->findOrFail($validated['livre_id']);

        if ($livre->quantite <= 0) {
            return response()->json([
                'message' => 'Quantité insuffisante.',
                'errors' => [
                    'livre_id' => ['Ce livre n\'est plus disponible.'],
                ],
            ], 422);
        }

        // Générer un token unique pour la réservation AVANT la transaction
        $reservationToken = Str::random(32);
        
        $emprunt = DB::transaction(function () use ($livre, $user, $reservationToken): Emprunt {
            $livre->decrement('quantite');

            $dateEmprunt = CarbonImmutable::today();
            $dateRetourPrevue = $dateEmprunt->addDays(self::DUREE_EMPRUNT_JOURS);

            $emprunt = Emprunt::create([
                'etudiant_id' => $user->id,
                'livre_id' => $livre->id,
                'date_emprunt' => $dateEmprunt,
                'date_retour_prevue' => $dateRetourPrevue,
                'statut' => Emprunt::STATUT_EN_COURS,
                'reservation_token' => hash('sha256', $reservationToken),
            ]);

            return $emprunt;
        });

        // Recharger l'emprunt
        $emprunt->refresh();
        
        // Générer le QR code APRÈS la transaction pour éviter les timeouts
        // et permettre une meilleure gestion des erreurs
        try {
            $this->genererQrCode($emprunt, $reservationToken);
            $emprunt->refresh(); // Recharger pour avoir le qr_code_path
        } catch (\Exception $e) {
            \Log::error("Erreur génération QR code (non bloquant): " . $e->getMessage());
            // Ne pas bloquer la création de l'emprunt si le QR code échoue
        }
        
        // Préparer la réponse avec l'URL du QR code et toutes les informations
        $emprunt->load(['livre', 'etudiant']);
        $responseData = $emprunt->toArray();
        $responseData['qr_code_url'] = $emprunt->qr_code_path 
            ? Storage::disk('public')->url($emprunt->qr_code_path)
            : null;
        $responseData['qr_code_available'] = !empty($emprunt->qr_code_path);
        
        // Ajouter un message informatif sur le QR code
        $responseData['qr_code_info'] = [
            'message' => 'Le QR code contient toutes les informations de votre réservation',
            'contenu' => [
                'Informations étudiant' => $emprunt->etudiant->name . ' (' . $emprunt->etudiant->email . ')',
                'Informations livre' => $emprunt->livre->titre . ' - ' . $emprunt->livre->auteur . ' (ISBN: ' . $emprunt->livre->isbn . ')',
                'Dates' => 'Emprunt: ' . $emprunt->date_emprunt->toDateString() . ' | Retour prévu: ' . $emprunt->date_retour_prevue->toDateString(),
            ],
        ];
        
        return response()->json([
            'message' => 'Emprunt créé avec succès. Un QR code a été généré avec toutes les informations de votre réservation.',
            'data' => $responseData,
        ], 201);
    }

    /**
     * Génère un QR code pour l'emprunt
     */
    private function genererQrCode(Emprunt $emprunt, string $token): void
    {
        try {
            $emprunt->load(['etudiant', 'livre']);

            // Données à encoder dans le QR code - Toutes les informations nécessaires
            $qrData = [
                'type' => 'reservation',
                'emprunt_id' => $emprunt->id,
                'token' => $token,
                // Informations de l'étudiant
                'etudiant' => [
                    'id' => $emprunt->etudiant_id,
                    'nom' => $emprunt->etudiant->name,
                    'email' => $emprunt->etudiant->email,
                ],
                // Informations du livre
                'livre' => [
                    'id' => $emprunt->livre_id,
                    'titre' => $emprunt->livre->titre,
                    'auteur' => $emprunt->livre->auteur,
                    'isbn' => $emprunt->livre->isbn,
                ],
                // Dates
                'date_emprunt' => $emprunt->date_emprunt->toDateString(),
                'date_retour_prevue' => $emprunt->date_retour_prevue->toDateString(),
                'timestamp' => now()->toIso8601String(),
            ];

            // Générer l'URL du QR code via API externe
            $qrUrl = QrCodeService::generateQrCodeUrl($qrData);
            
            \Log::info("Génération QR code pour emprunt {$emprunt->id}, URL: " . substr($qrUrl, 0, 100));
            
            // Sauvegarder le QR code
            $qrPath = "qr_codes/emprunt_{$emprunt->id}.png";
            
            // S'assurer que le dossier existe
            $qrDir = dirname($qrPath);
            if (!Storage::disk('public')->exists($qrDir)) {
                Storage::disk('public')->makeDirectory($qrDir);
            }
            
            $saved = QrCodeService::downloadAndSaveQrCode($qrUrl, $qrPath);

            // Mettre à jour l'emprunt avec le chemin du QR code
            $emprunt->update([
                'qr_code_path' => $saved ? $qrPath : null,
                'qr_generated_at' => now(),
            ]);
            
            if ($saved) {
                \Log::info("QR code généré avec succès pour l'emprunt {$emprunt->id} à {$qrPath}");
            } else {
                \Log::warning("Échec de la génération du QR code pour l'emprunt {$emprunt->id}. URL: {$qrUrl}");
                // Essayer une deuxième fois après un court délai
                sleep(1);
                $saved = QrCodeService::downloadAndSaveQrCode($qrUrl, $qrPath);
                if ($saved) {
                    $emprunt->update(['qr_code_path' => $qrPath]);
                    \Log::info("QR code généré avec succès au deuxième essai pour l'emprunt {$emprunt->id}");
                }
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la génération du QR code: " . $e->getMessage());
            // Ne pas bloquer la création de l'emprunt si le QR code échoue
            $emprunt->update([
                'qr_generated_at' => now(),
            ]);
        }
    }

    /**
     * Télécharger le QR code de réservation (pour l'étudiant)
     */
    public function downloadQrCode(Request $request, int $id): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $emprunt = Emprunt::where('id', $id)
            ->where('etudiant_id', $user->id)
            ->firstOrFail();

        if (!$emprunt->qr_code_path || !Storage::disk('public')->exists($emprunt->qr_code_path)) {
            return response()->json([
                'message' => 'QR code non disponible.',
                'errors' => [
                    'qr_code' => ['Le QR code pour cet emprunt n\'existe pas.'],
                ],
            ], 404);
        }

        return Storage::disk('public')->download(
            $emprunt->qr_code_path,
            "qr_reservation_{$emprunt->id}.png"
        );
    }

    /**
     * Obtenir les informations du QR code (pour affichage)
     */
    public function getQrCodeInfo(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $emprunt = Emprunt::with(['livre', 'etudiant'])
            ->where('id', $id)
            ->where('etudiant_id', $user->id)
            ->firstOrFail();

        // Si le QR code n'existe pas, essayer de le régénérer
        if (!$emprunt->qr_code_path || !Storage::disk('public')->exists($emprunt->qr_code_path)) {
            // Régénérer le QR code si on a le token (pour les nouveaux emprunts)
            if ($emprunt->reservation_token) {
                // On ne peut pas régénérer sans le token original, mais on peut créer un nouveau token
                $newToken = Str::random(32);
                $emprunt->update([
                    'reservation_token' => hash('sha256', $newToken),
                ]);
                $this->genererQrCode($emprunt, $newToken);
                $emprunt->refresh();
            }
        }

        return response()->json([
            'message' => 'Informations de réservation récupérées.',
            'data' => [
                'emprunt' => $emprunt,
                'qr_code_url' => $emprunt->qr_code_path 
                    ? Storage::disk('public')->url($emprunt->qr_code_path)
                    : null,
                'qr_code_available' => !empty($emprunt->qr_code_path) && Storage::disk('public')->exists($emprunt->qr_code_path),
                'qr_generated_at' => $emprunt->qr_generated_at,
            ],
        ]);
    }
    
    /**
     * Régénérer le QR code pour un emprunt (si manquant)
     */
    public function regenerateQrCode(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $emprunt = Emprunt::with(['livre', 'etudiant'])
            ->where('id', $id)
            ->where('etudiant_id', $user->id)
            ->firstOrFail();

        // Générer un nouveau token
        $newToken = Str::random(32);
        $emprunt->update([
            'reservation_token' => hash('sha256', $newToken),
        ]);

        // Régénérer le QR code
        $this->genererQrCode($emprunt, $newToken);
        $emprunt->refresh();

        return response()->json([
            'message' => 'QR code régénéré avec succès.',
            'data' => [
                'emprunt' => $emprunt,
                'qr_code_url' => $emprunt->qr_code_path 
                    ? Storage::disk('public')->url($emprunt->qr_code_path)
                    : null,
                'qr_code_available' => !empty($emprunt->qr_code_path) && Storage::disk('public')->exists($emprunt->qr_code_path),
            ],
        ]);
    }

    public function retour(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'emprunt_id' => ['required', 'integer', 'exists:emprunts,id'],
        ]);

        /** @var Emprunt $emprunt */
        $emprunt = Emprunt::where('id', $validated['emprunt_id'])
            ->where('etudiant_id', $user->id)
            ->firstOrFail();

        if (!in_array($emprunt->statut, [Emprunt::STATUT_EN_COURS, Emprunt::STATUT_RETARD], true)) {
            return response()->json([
                'message' => 'Retour non autorisé pour cet emprunt.',
                'errors' => [
                    'emprunt_id' => ['Cet emprunt ne peut pas être marqué en attente de retour.'],
                ],
            ], 422);
        }

        $emprunt->update([
            'statut' => Emprunt::STATUT_EN_ATTENTE_RETOUR,
        ]);

        return response()->json([
            'message' => 'Retour en attente de validation.',
            'data' => $emprunt,
        ]);
    }
}







