<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PatientVisit extends Model
{
    protected $table = 'patient_visits';

    protected $fillable = [
        'employee_id', // Murojaat qilgan xodim (Employee)
        'doctor_id',   // Qabul qilgan shifokor (User)
        'visit_type',  // Tashrif turi: self | brigadier | medical_exam
        'visited_at',  // Qabul sanasi va vaqti
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    // Murojaat qilgan xodim
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Qabul qilgan shifokor
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // Ko'rik natijalari (harorat, qon bosimi, puls va h.k.)
    public function examination(): HasOne
    {
        return $this->hasOne(PatientExamination::class, 'visit_id');
    }

    // Tashrif davomida aniqlangan kasalliklar pivot yozuvlari
    public function visitDiseases(): HasMany
    {
        return $this->hasMany(VisitDiseases::class, 'visit_id');
    }

    // Tashrif davomida aniqlangan kasalliklar (many-to-many)
    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(Diseases::class, 'visit_diseases', 'visit_id', 'disease_id');
    }

    // Tashrif davomida tayinlangan dorilar pivot yozuvlari
    public function visitMedicines(): HasMany
    {
        return $this->hasMany(VisitMedicine::class, 'visit_id');
    }

    // Tashrif davomida tayinlangan dorilar (many-to-many)
    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(Medicine::class, 'visit_medicines', 'visit_id', 'medicine_id')
            ->withPivot('quantity', 'dosage', 'instruction');
    }

    // Tashrif asosida berilgan kasallik varaqasi
    public function sickLeave(): HasOne
    {
        return $this->hasOne(SickLeave::class, 'visit_id');
    }
}