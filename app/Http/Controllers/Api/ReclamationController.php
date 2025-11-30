<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReclamationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Reclamation::query()->with('etudiant');

        if ($user->role === 'etudiant') {
            $query->where('etudiant_id', $user->id);
        }

        $reclamations = $query
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($reclamations);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'etudiant') {
            return response()->json([
                'message' => 'Forbidden.',
                'errors' => [
                    'role' => ['Seuls les étudiants peuvent créer une réclamation.'],
                ],
            ], 403);
        }

        $validated = $request->validate([
            'sujet' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $reclamation = Reclamation::create([
            'etudiant_id' => $user->id,
            'sujet' => $validated['sujet'],
            'message' => $validated['message'],
        ]);

        return response()->json([
            'message' => 'Réclamation créée avec succès.',
            'data' => $reclamation,
        ], 201);
    }
}







