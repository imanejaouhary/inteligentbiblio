<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'livre_id',
        'user_id',
        'note',
        'commentaire',
    ];

    public function livre(): BelongsTo
    {
        return $this->belongsTo(Livre::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}







