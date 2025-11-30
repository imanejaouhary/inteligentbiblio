<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cours', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('prof_id')->constrained('users')->cascadeOnDelete();
            $table->string('fichier_path');
            $table->unsignedBigInteger('taille')->default(0);
            $table->string('extension', 10);
            $table->timestamps();
        });

        Schema::create('cours_filieres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cours_id')->constrained('cours')->cascadeOnDelete();
            $table->enum('filiere', ['IL', 'ADIA']);
            $table->timestamps();

            $table->index(['cours_id', 'filiere']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cours_filieres');
        Schema::dropIfExists('cours');
    }
};







