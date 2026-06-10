<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\EmployeeStaff;

class Employee extends BaseModel
{
    use SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'middle_name',
        'tabel',
        'inn',
        'inps',
        'phone',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(PatientVisit::class);
    }

    public function employeeStaff(): HasMany
    {
        return $this->hasMany(EmployeeStaff::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->first_name} {$this->middle_name}");
    }
}