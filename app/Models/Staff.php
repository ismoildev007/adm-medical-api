<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends BaseModel
{
    use SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'id',
        'department_id',
        'position_id',
        'is_department_director',
    ];

    protected $casts = [
        'is_department_director' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function employeeStaff(): HasMany
    {
        return $this->hasMany(EmployeeStaff::class);
    }
}