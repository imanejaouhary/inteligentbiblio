<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Livre extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'auteur',
        'isbn',
        'quantite',
        'description',
        'image_path',
        'disponible_numerique',
        'fichier_path',
        'format',
        'taille_fichier',
    ];

    public function emprunts(): HasMany
    {
        return $this->hasMany(Emprunt::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }
}







