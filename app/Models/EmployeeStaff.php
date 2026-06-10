<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeStaff extends BaseModel
{
    use SoftDeletes;

    protected $table = 'employee_staff';

    protected $fillable = [
        'id',
        'staff_id',
        'employee_id',
        'department_id',
        'position_id',
        'main_staff',
    ];

    protected $casts = [
        'main_staff' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}