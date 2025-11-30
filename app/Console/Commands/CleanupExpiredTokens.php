<?php

namespace App\Console\Commands;

use App\Models\RefreshToken;
use Illuminate\Console\Command;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:cleanup-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime les refresh tokens expirés.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = RefreshToken::where('expires_at', '<=', now())->delete();

        $this->info(sprintf('Refresh tokens expirés supprimés : %d', $deleted));

        return self::SUCCESS;
    }
}







