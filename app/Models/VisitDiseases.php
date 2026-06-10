<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitDiseases extends Model
{
    protected $table = 'visit_diseases';

    protected $fillable = [
        'visit_id',   // Tashrif (PatientVisit)
        'disease_id', // Aniqlangan kasallik (Diseases)
    ];

    // Ushbu yozuv tegishli tashrif
    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    // Ushbu yozuvdagi kasallik
    public function disease(): BelongsTo
    {
        return $this->belongsTo(Diseases::class, 'disease_id');
    }
}