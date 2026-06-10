<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends BaseModel
{
    use SoftDeletes;

    protected $table = 'departments';

    protected $fillable = [
        'id',
        'code',
        'parent_id',
        'department_type_id',
        'sequence',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(DepartmentTranslation::class, 'object_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}