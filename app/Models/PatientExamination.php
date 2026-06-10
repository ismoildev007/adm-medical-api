<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientExamination extends Model
{
    protected $table = 'patient_examinations';

    protected $fillable = [
        'visit_id',       // Tegishli tashrif (PatientVisit)
        'temperature',    // Tana harorati (°C)
        'blood_pressure', // Qon bosimi ("120/80" formatida)
        'pulse',          // Puls (minutiga urish soni)
        'complaints',     // Bemorning shikoyatlari
        'doctor_notes',   // Shifokorning xulosasi va tavsiyalari
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'pulse'       => 'integer',
    ];

    // Ko'rik amalga oshirilgan tashrif
    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }
}