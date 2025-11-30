<?php

namespace App\Http\Requests\Livre;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLivreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'titre' => ['sometimes', 'string', 'max:255'],
            'auteur' => ['sometimes', 'string', 'max:255'],
            'isbn' => ['sometimes', 'string', 'max:50', 'unique:livres,isbn,' . $id],
            'quantite' => ['sometimes', 'integer', 'min:0'],
            'description' => ['sometimes', 'string'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'disponible_numerique' => ['sometimes', 'boolean'],
        ];
    }
}







