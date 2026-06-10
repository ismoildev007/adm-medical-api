<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $table = 'medicines';

    protected $fillable = [
        'name',
        'unit',
        'quantity',
        'expire_date',
        'description',
        'is_active',
    ];

    protected $casts = [
        'quantity'    => 'integer',
        'expire_date' => 'date',
        'is_active'   => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(MedicineTransaction::class);
    }

    public function visitMedicines(): HasMany
    {
        return $this->hasMany(VisitMedicine::class);
    }
}