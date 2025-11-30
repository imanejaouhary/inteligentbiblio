<?php

namespace App\Http\Requests\Livre;

use Illuminate\Foundation\Http\FormRequest;

class StoreLivreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'auteur' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:50', 'unique:livres,isbn'],
            'quantite' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'disponible_numerique' => ['sometimes', 'boolean'],
        ];
    }
}







