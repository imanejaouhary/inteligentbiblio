<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emprunts', function (Blueprint $table) {
            $table->string('reservation_token', 64)->unique()->nullable()->after('statut');
            $table->string('qr_code_path')->nullable()->after('reservation_token');
            $table->timestamp('qr_generated_at')->nullable()->after('qr_code_path');
        });
    }

    public function down(): void
    {
        Schema::table('emprunts', function (Blueprint $table) {
            $table->dropColumn(['reservation_token', 'qr_code_path', 'qr_generated_at']);
        });
    }
};

