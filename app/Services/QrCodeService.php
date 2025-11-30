<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrCodeService
{
    /**
     * Génère un QR code en utilisant une API externe ou une bibliothèque alternative
     * Pour l'instant, on génère juste les données et on laisse le frontend générer le QR
     */
    public static function generateQrData(array $data): array
    {
        return [
            'data' => $data,
            'token' => Str::random(32),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Alternative : Utiliser une API externe pour générer le QR code
     * Exemple avec qrcode.tec-it.com ou api.qrserver.com
     */
    public static function generateQrCodeUrl(array $data): string
    {
        $encodedData = urlencode(json_encode($data));
        $size = 400;
        
        // Utiliser une API publique gratuite
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}";
    }

    /**
     * Télécharger le QR code depuis l'API et le sauvegarder
     */
    public static function downloadAndSaveQrCode(string $url, string $savePath): bool
    {
        try {
            \Log::info("Téléchargement QR code depuis: " . substr($url, 0, 100));
            
            // Utiliser curl si disponible pour plus de contrôle
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                $qrContent = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($error) {
                    \Log::error("Erreur cURL lors du téléchargement QR code: {$error}");
                    return false;
                }
                
                if ($httpCode !== 200 || $qrContent === false) {
                    \Log::warning("Échec du téléchargement QR code: HTTP $httpCode, URL: " . substr($url, 0, 100));
                    return false;
                }
            } else {
                // Fallback sur file_get_contents
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 30,
                        'ignore_errors' => true,
                        'user_agent' => 'Mozilla/5.0',
                    ],
                ]);
                $qrContent = @file_get_contents($url, false, $context);
                
                if ($qrContent === false) {
                    \Log::warning("Échec du téléchargement QR code avec file_get_contents, URL: " . substr($url, 0, 100));
                    return false;
                }
            }

            // Vérifier que c'est bien une image PNG
            if (strlen($qrContent) < 100) {
                \Log::warning("QR code téléchargé trop petit (" . strlen($qrContent) . " bytes), probablement une erreur");
                return false;
            }
            
            // Vérifier que c'est bien une image PNG (commence par PNG signature)
            if (substr($qrContent, 0, 8) !== "\x89PNG\r\n\x1a\n" && substr($qrContent, 0, 3) !== "\xFF\xD8\xFF") {
                \Log::warning("Le contenu téléchargé ne semble pas être une image valide");
                // Continuer quand même, parfois l'API retourne l'image directement
            }

            // S'assurer que le dossier existe
            $directory = dirname($savePath);
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $saved = Storage::disk('public')->put($savePath, $qrContent);
            
            if ($saved) {
                \Log::info("QR code sauvegardé avec succès: {$savePath} (" . strlen($qrContent) . " bytes)");
            } else {
                \Log::error("Échec de la sauvegarde du QR code: {$savePath}");
            }
            
            return $saved !== false;
        } catch (\Exception $e) {
            \Log::error('Erreur lors du téléchargement du QR code: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}

