<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reclamations', function (Blueprint $table) {
            $table->text('reponse')->nullable()->after('message');
            $table->foreignId('biblio_id')->nullable()->after('reponse')->constrained('users')->nullOnDelete();
            $table->timestamp('repondu_at')->nullable()->after('biblio_id');
        });
    }

    public function down(): void
    {
        Schema::table('reclamations', function (Blueprint $table) {
            $table->dropForeign(['biblio_id']);
            $table->dropColumn(['reponse', 'biblio_id', 'repondu_at']);
        });
    }
};

