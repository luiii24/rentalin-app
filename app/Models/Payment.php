<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'rental_id',
        'payment_method',
        'amount',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'integer',
        'paid_at' => 'datetime',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    // Format penyimpanan: "QRIS" atau "QRIS|payment_proofs/RNTL-XXXX.jpg"
    public function getMethodLabelAttribute(): string
    {
        return explode('|', (string) $this->payment_method)[0] ?: 'QRIS';
    }

    public function getProofImageAttribute(): ?string
    {
        $parts = explode('|', (string) $this->payment_method, 2);
        return !empty($parts[1]) ? $parts[1] : null;
    }

    public function getStatusAttribute(): string
    {
        return $this->payment_status;
    }
}