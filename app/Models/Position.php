<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends BaseModel
{
    use SoftDeletes;

    protected $table = 'positions';

    protected $fillable = [
        'id',
        'code',
        'position_type_id',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PositionTranslation::class, 'object_id');
    }
}
