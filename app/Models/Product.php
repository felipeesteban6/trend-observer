<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'supplier_trending_product_id', 'name', 'slug', 'description', 'image_url',
        'category', 'cost_price', 'sale_price', 'currency', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_published' => 'boolean',
        ];
    }

    public function supplierTrendingProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierTrendingProduct::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
