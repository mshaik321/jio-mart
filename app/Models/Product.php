<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ReviewStatus;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id', 'brand_id', 'unit_id', 'sku', 'name', 'slug', 'description',
    'short_description', 'weight', 'mrp', 'selling_price', 'tax_rate',
    'min_order_qty', 'max_order_qty', 'status', 'is_featured', 'is_bestseller', 'is_new_arrival',
    'sort_order',
])]
class Product extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_new_arrival' => 'boolean',
            'mrp' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'weight' => 'decimal:3',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', ReviewStatus::Approved);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    /**
     * Free-text match on the English name/SKU/description and on the name in
     * every translated locale, so "Toor Dal" and "కందిపప్పు" find the same product.
     */
    public function scopeMatchingTerm(Builder $query, string $term): void
    {
        $query->where(function (Builder $inner) use ($term) {
            $inner->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereHas('translations', fn (Builder $translation) => $translation->where('name', 'like', "%{$term}%"));
        });
    }
}
