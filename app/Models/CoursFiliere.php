<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursFiliere extends Model
{
    use HasFactory;

    protected $table = 'cours_filieres';

    protected $fillable = [
        'cours_id',
        'filiere',
    ];

    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class);
    }
}







