<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductScore extends Model
{
    protected $fillable = [
        'search_keyword_id', 'supplier_trending_product_id', 'label',
        'trend_growth_score', 'supplier_signal_score', 'total_score', 'computed_on',
    ];

    protected function casts(): array
    {
        return [
            'trend_growth_score' => 'decimal:2',
            'supplier_signal_score' => 'decimal:2',
            'total_score' => 'decimal:2',
            'computed_on' => 'date',
        ];
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(SearchKeyword::class, 'search_keyword_id');
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierTrendingProduct::class, 'supplier_trending_product_id');
    }
}
