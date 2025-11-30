<?php

namespace App\Console\Commands;

use App\Models\Cours;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:cleanup-storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoie les fichiers orphelins dans storage/app/cours qui ne sont plus référencés par la table cours.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = config('filesystems.default', 'local');
        $directory = 'cours';

        $this->info(sprintf('Nettoyage du stockage sur le disque "%s" dans le dossier "%s"...', $disk, $directory));

        $allFiles = collect(Storage::disk($disk)->files($directory));

        $usedPaths = Cours::pluck('fichier_path')->filter()->values();

        $orphans = $allFiles->diff($usedPaths);

        $deletedCount = 0;

        foreach ($orphans as $file) {
            if (Storage::disk($disk)->delete($file)) {
                $deletedCount++;
            }
        }

        $this->info(sprintf('Fichiers orphelins supprimés : %d', $deletedCount));

        return self::SUCCESS;
    }
}







