<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cours extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'prof_id',
        'fichier_path',
        'taille',
        'extension',
    ];

    public function prof(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prof_id');
    }

    public function filieres(): HasMany
    {
        return $this->hasMany(CoursFiliere::class, 'cours_id');
    }
}


