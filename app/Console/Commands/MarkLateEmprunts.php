<?php

namespace App\Console\Commands;

use App\Models\Emprunt;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class MarkLateEmprunts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:mark-late';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marque les emprunts en retard en fonction de la date de retour prévue.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = CarbonImmutable::today();

        $updated = Emprunt::whereIn('statut', [Emprunt::STATUT_EN_COURS, Emprunt::STATUT_EN_ATTENTE_RETOUR])
            ->whereDate('date_retour_prevue', '<', $today)
            ->update(['statut' => Emprunt::STATUT_RETARD]);

        $this->info(sprintf('Emprunts marqués en retard : %d', $updated));

        return self::SUCCESS;
    }
}







