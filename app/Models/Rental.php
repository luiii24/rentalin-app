<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Rental extends Model
{
    protected $fillable = [
        'booking_code',
        'vehicle_id',
        'admin_id',
        'customer_name',
        'customer_phone',
        'pickup_date',
        'duration_days',
        'total_price',
        'ktp_verified',
        'status',
    ];

    protected $casts = [
        'pickup_date'   => 'datetime',
        'duration_days' => 'integer',
        'total_price'   => 'integer',
        'ktp_verified'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Rental $rental) {
            if (empty($rental->booking_code)) {
                do {
                    $code = 'RNTL-' . strtoupper(Str::random(6));
                } while (static::where('booking_code', $code)->exists());

                $rental->booking_code = $code;
            }
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // Alias kompatibel dengan view invoice.blade.php & transactions.blade.php
    public function getTotalAmountAttribute(): int
    {
        return (int) $this->total_price;
    }

    public function getPaymentProofAttribute(): ?string
    {
        return $this->payment?->proof_image;
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->payment?->payment_status === 'paid') {
            return 'paid';
        }
        if (!empty($this->payment?->proof_image)) {
            return 'menunggu_konfirmasi';
        }
        return 'pending';
    }

    public function getCollateralTypeAttribute(): ?string
    {
        if (!$this->ktp_verified) {
            return null;
        }

        $files = Storage::disk('public')->files('collaterals');
        foreach ($files as $file) {
            $basename = pathinfo($file, PATHINFO_FILENAME);
            if (str_starts_with($basename, $this->booking_code . '__')) {
                return str_replace('_', ' ', explode('__', $basename)[1] ?? 'KTP Asli');
            }
        }

        return 'KTP Asli';
    }
}