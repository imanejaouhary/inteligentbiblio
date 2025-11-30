<?php

namespace App\Console\Commands;

use App\Models\Emprunt;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendFakeNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:send-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des notifications simulées (logs) pour les emprunts bientôt en retard.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $threshold = CarbonImmutable::today()->addDays(2);

        $emprunts = Emprunt::with('etudiant', 'livre')
            ->where('statut', Emprunt::STATUT_EN_COURS)
            ->whereDate('date_retour_prevue', '<=', $threshold)
            ->get();

        foreach ($emprunts as $emprunt) {
            /** @var User $etudiant */
            $etudiant = $emprunt->etudiant;

            $message = sprintf(
                '[NOTIF] Rappel pour %s (%s) - Livre "%s" à retourner avant le %s',
                $etudiant->name,
                $etudiant->email,
                $emprunt->livre->titre,
                $emprunt->date_retour_prevue->format('Y-m-d')
            );

            Log::info($message);
            $this->line($message);
        }

        $this->info(sprintf('Notifications simulées envoyées pour %d emprunts.', $emprunts->count()));

        return self::SUCCESS;
    }
}







