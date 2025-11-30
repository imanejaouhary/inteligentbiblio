<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'in:admin,bibliothecaire,prof,etudiant'],
            'filiere' => ['nullable', 'in:IL,ADIA'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = $this->input('role', 'etudiant');
            $filiere = $this->input('filiere');

            if ($role === 'etudiant' && !$filiere) {
                $validator->errors()->add('filiere', 'La filière est obligatoire pour un étudiant.');
            }
        });
    }
}







