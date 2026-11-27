<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'spec',
        'license_plate',
        'price_per_day',
        'total_stock',
        'available_stock',
        'status',
        'images',
    ];

    protected $casts = [
        'images'          => 'array',
        'price_per_day'   => 'integer',
        'total_stock'     => 'integer',
        'available_stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    // Memisahkan spesifikasi utama & warna/tahun yang disimpan dalam kolom spec
    public function getCleanSpecAttribute(): string
    {
        return explode(' | ', (string) $this->spec)[0] ?? '';
    }

    public function getColorYearAttribute(): string
    {
        return explode(' | ', (string) $this->spec)[1] ?? '';
    }

    public function getImageUrlsAttribute(): array
    {
        if (!is_array($this->images) || count($this->images) === 0) {
            return ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=' . urlencode($this->name)];
        }

        return array_map(
            fn ($img) => (str_starts_with($img, 'http') || str_starts_with($img, 'data:'))
                ? $img
                : asset('storage/' . $img),
            $this->images
        );
    }
}