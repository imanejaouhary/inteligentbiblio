<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string'],
        ]);

        $q = $request->get('q');

        $livres = Livre::query()
            ->where(function ($query) use ($q): void {
                $query->where('titre', 'like', "%{$q}%")
                    ->orWhere('auteur', 'like', "%{$q}%")
                    ->orWhere('isbn', 'like', "%{$q}%");
            })
            ->paginate($request->integer('per_page', 20));

        return response()->json($livres);
    }
}







