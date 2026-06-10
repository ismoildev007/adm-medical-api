<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SickLeave extends Model
{
    protected $table = 'sick_leaves';

    protected $fillable = [
        'visit_id',   // Asosiy tashrif (PatientVisit)
        'start_date', // Kasallik varaqasi boshlanadigan sana
        'end_date',   // Kasallik varaqasi tugaydigan sana
        'reason',     // Tibbiy sabab yoki shifokor xulosasi
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Kasallik varaqasi berilgan tashrif
    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }
}