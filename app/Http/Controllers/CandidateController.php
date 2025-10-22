<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    /**
     * Affiche la liste de tous les candidats
     */
    public function index()
    {
        $candidates = Candidate::all()->map(function($candidate) {
            $candidate->photo_url = $candidate->getPhotoUrl();
            return $candidate;
        });

        return response()->json($candidates, 200);
    }

    /**
     * Affiche le formulaire de création (inutile pour une API)
     */
    public function create()
    {
        return response()->json(['message' => 'Non utilisé dans une API.'], 404);
    }

    /**
     * Enregistre un nouveau candidat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'nationalite' => 'required|string|max:100',
            'age' => 'nullable|integer|min:0|max:120',
            'poids' => 'nullable|numeric|min:0',
            'taille' => 'nullable|numeric|min:0',
            'short_description' => 'nullable|string|max:255',
            'full_description' => 'nullable|string',
            'photo_path' => 'nullable|file|image|max:2048', // validation du fichier image
        ]);

        // Gestion du fichier
        if ($request->hasFile('photo_path')) {
            $file = $request->file('photo_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $validated['photo_path'] = $file->storeAs('candidates', $fileName, 'public');
        }

        $candidate = Candidate::create($validated);

        return response()->json([
            'message' => 'Candidat créé avec succès !',
            'data' => $candidate
        ], 201);
    }

    /**
     * Affiche les détails d’un candidat
     */
    public function show(string $id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        $candidate->photo_url = $candidate->getPhotoUrl();

        return response()->json($candidate, 200);
    }

    /**
     * Affiche le formulaire d’édition (inutile pour une API)
     */
    public function edit(string $id)
    {
        return response()->json(['message' => 'Non utilisé dans une API.'], 404);
    }

    /**
     * Met à jour les informations d’un candidat
     */
    public function update(Request $request, string $id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'nationalite' => 'sometimes|string|max:100',
            'age' => 'sometimes|integer|min:0|max:120',
            'poids' => 'sometimes|numeric|min:0',
            'taille' => 'sometimes|numeric|min:0',
            'short_description' => 'sometimes|string|max:255',
            'full_description' => 'sometimes|string',
            'photo_path' => 'sometimes|file|image|max:2048',
        ]);

        // Gestion du fichier (supprime l'ancien si nouveau upload)
        if ($request->hasFile('photo_path')) {
            if ($candidate->photo_path && Storage::disk('public')->exists($candidate->photo_path)) {
                Storage::disk('public')->delete($candidate->photo_path);
            }

            $file = $request->file('photo_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $validated['photo_path'] = $file->storeAs('candidates', $fileName, 'public');
        }

        $candidate->update($validated);

        return response()->json([
            'message' => 'Candidat mis à jour avec succès !',
            'data' => $candidate
        ], 200);
    }

    /**
     * Supprime un candidat
     */
    public function destroy(string $id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        // Supprimer la photo si elle existe
        if ($candidate->photo_path && Storage::disk('public')->exists($candidate->photo_path)) {
            Storage::disk('public')->delete($candidate->photo_path);
        }

        $candidate->delete();

        return response()->json(['message' => 'Candidat supprimé avec succès'], 200);
    }
}
