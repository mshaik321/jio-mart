<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'product_id', 'sku', 'variant_name', 'attributes', 'barcode',
    'mrp', 'selling_price', 'is_default', 'is_active',
])]
class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'mrp' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Default, sellable variants of active products, eager-loading everything a
     * product card renders (image, translated names, stock) so a listing page
     * doesn't query per card.
     */
    public function scopeListable(Builder $query): void
    {
        $query->where('product_variants.is_default', true)
            ->where('product_variants.is_active', true)
            ->whereHas('product', fn (Builder $product) => $product->where('status', ProductStatus::Active))
            ->with(['product.primaryImage', 'product.translations', 'inventories']);
    }

    /**
     * Catalog order: the product's merchandising sort order, then newest variant id.
     */
    public function scopeInCatalogOrder(Builder $query): void
    {
        $query->orderBy(Product::select('sort_order')->whereColumn('products.id', 'product_variants.product_id'))
            ->orderBy('product_variants.id');
    }

    public function totalStock(): int
    {
        // Listing pages eager-load inventories; summing the loaded rows avoids two queries per product card.
        if ($this->relationLoaded('inventories')) {
            return (int) $this->inventories->sum('quantity_on_hand') - (int) $this->inventories->sum('quantity_reserved');
        }

        return (int) $this->inventories()->sum('quantity_on_hand')
            - (int) $this->inventories()->sum('quantity_reserved');
    }
}
