<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Emprunt extends Model
{
    use HasFactory;

    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_EN_ATTENTE_RETOUR = 'en_attente_retour';
    public const STATUT_RETOURNE = 'retourne';
    public const STATUT_RETARD = 'retard';

    protected $fillable = [
        'etudiant_id',
        'livre_id',
        'date_emprunt',
        'date_retour_prevue',
        'date_retour_effective',
        'statut',
        'reservation_token',
        'qr_code_path',
        'qr_generated_at',
    ];

    protected $casts = [
        'date_emprunt' => 'date',
        'date_retour_prevue' => 'date',
        'date_retour_effective' => 'date',
        'qr_generated_at' => 'datetime',
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function livre(): BelongsTo
    {
        return $this->belongsTo(Livre::class);
    }
}







