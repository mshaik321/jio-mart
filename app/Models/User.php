<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone'])]
#[Hidden(['password', 'remember_token', 'google_id'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Every role that grants access to the /admin panel.
     */
    public const STAFF_ROLES = [
        'Super Admin', 'Admin', 'Store Manager', 'Inventory Manager',
        'Order Manager', 'Delivery Manager', 'Delivery Agent',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function deliveryAgent(): HasOne
    {
        return $this->hasOne(DeliveryAgent::class);
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(self::STAFF_ROLES);
    }

    /**
     * The shopping profile (cart, wishlist, addresses, orders hang off it),
     * created on first use. Registration and Google sign-in create it up front;
     * staff and admin-created accounts get one the first time they shop, so
     * "My Orders" / "My Addresses" never hit a missing profile.
     */
    public function customerProfile(): Customer
    {
        if ($this->customer) {
            return $this->customer;
        }

        $customer = Customer::withTrashed()->firstOrCreate(['user_id' => $this->id]);

        if ($customer->trashed()) {
            $customer->restore();
        }

        $this->setRelation('customer', $customer);

        return $customer;
    }
}
