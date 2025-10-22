<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'nationalite',
        'age',
        'poids',
        'taille',
        'short_description',
        'full_description',
        'photo_path',
    ];

    /**
     *  Retourne le nom complet du candidat
     */
    public function getFullName(): string
    {
        return ucfirst($this->prenom) . ' ' . strtoupper($this->nom);
    }

    /**
     * Retourne l'URL publique de la photo du candidat
     */
    public function getPhotoUrl(): ?string
    {
        return $this->photo_path ? url('storage/' . $this->photo_path) : null;
    }

    /**
     * Retourne la description courte (limite à 80 caractères)
     */
    public function getShortDescription(int $limit = 80): string
    {
        return strlen($this->short_description) > $limit
            ? substr($this->short_description, 0, $limit) . '...'
            : $this->short_description;
    }

    /**
     *  Retourne un résumé de la fiche du candidat
     */
    public function summary(): string
    {
        return "{$this->getFullName()} ({$this->nationalite}, {$this->age} ans)";
    }


}
