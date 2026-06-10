<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'department_translations';

    protected $fillable = [
        'object_id',
        'language_code',
        'name',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'object_id');
    }
}