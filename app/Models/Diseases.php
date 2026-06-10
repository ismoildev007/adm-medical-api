<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diseases extends Model
{
    protected $table = 'diseases';

    protected $fillable = [
        'name',        // Kasallik nomi (masalan: Gripp, Gipertenziya)
        'description', // Kasallik tavsifi va simptomlar
    ];

    // Ushbu kasallik qayd etilgan barcha pivot yozuvlar
    public function visitDiseases(): HasMany
    {
        return $this->hasMany(VisitDiseases::class, 'disease_id');
    }

    // Ushbu kasallik aniqlangan barcha tashrif
    public function visits(): BelongsToMany
    {
        return $this->belongsToMany(PatientVisit::class, 'visit_diseases', 'disease_id', 'visit_id');
    }
}