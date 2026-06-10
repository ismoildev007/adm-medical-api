<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineTransaction extends Model
{
    protected $table = 'medicine_transactions';

    protected $fillable = [
        'medicine_id', // Qaysi dori (Medicine)
        'type',        // Tranzaksiya turi: income | outcome | write_off
        'quantity',    // Miqdor
        'comment',     // Izoh
        'created_by',  // Amalga oshirgan foydalanuvchi (User)
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Tranzaksiya amalga oshirilgan dori
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    // Tranzaksiyani amalga oshirgan foydalanuvchi
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}