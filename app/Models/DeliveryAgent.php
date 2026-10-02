<?php

namespace App\Models;

use App\Enums\DeliveryAgentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'name', 'phone', 'vehicle_type', 'vehicle_number',
    'status', 'current_lat', 'current_lng', 'is_active',
])]
class DeliveryAgent extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => DeliveryAgentStatus::class,
            'current_lat' => 'decimal:7',
            'current_lng' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
