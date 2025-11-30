<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reclamation extends Model
{
    use HasFactory;

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_RESOLU = 'resolu';

    protected $fillable = [
        'etudiant_id',
        'sujet',
        'message',
        'statut',
        'reponse',
        'biblio_id',
        'repondu_at',
    ];

    protected $casts = [
        'repondu_at' => 'datetime',
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function bibliothecaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'biblio_id');
    }
}







