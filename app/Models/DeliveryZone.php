<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'pincodes', 'delivery_charge', 'free_delivery_above', 'is_active'])]
class DeliveryZone extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'pincodes' => 'array',
            'delivery_charge' => 'decimal:2',
            'free_delivery_above' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function servicesPincode(string $pincode): bool
    {
        return in_array($pincode, $this->pincodes ?? [], true);
    }

    public static function findServicing(string $pincode): ?self
    {
        return static::query()->where('is_active', true)->get()
            ->first(fn (self $zone) => $zone->servicesPincode($pincode));
    }
}
