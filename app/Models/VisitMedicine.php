<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitMedicine extends Model
{
    protected $table = 'visit_medicines';

    protected $fillable = [
        'visit_id',    // Tashrif (PatientVisit)
        'medicine_id', // Tayinlangan dori (Medicine)
        'quantity',    // Berilgan miqdor
        'dosage',      // Bir martalik doza (masalan: "1 dona", "5 ml")
        'instruction', // Qabul qilish ko'rsatmasi
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Dori tayinlangan tashrif
    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    // Tayinlangan dori
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}