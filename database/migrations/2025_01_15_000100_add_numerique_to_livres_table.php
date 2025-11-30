<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            $table->boolean('disponible_numerique')->default(false)->after('quantite');
            $table->string('fichier_path')->nullable()->after('image_path');
            $table->enum('format', ['pdf', 'epub', 'mobi'])->nullable()->after('fichier_path');
            $table->bigInteger('taille_fichier')->nullable()->after('format'); // en bytes
        });
    }

    public function down(): void
    {
        Schema::table('livres', function (Blueprint $table) {
            $table->dropColumn(['disponible_numerique', 'fichier_path', 'format', 'taille_fichier']);
        });
    }
};

