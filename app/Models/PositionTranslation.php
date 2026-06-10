<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionTranslation extends Model
{
    public $timestamps = false;

    protected $table = 'position_translations';

    protected $fillable = [
        'object_id',
        'language_code',
        'name',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'object_id');
    }
}
