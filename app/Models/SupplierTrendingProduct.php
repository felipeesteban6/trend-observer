<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierTrendingProduct extends Model
{
    protected $fillable = [
        'supplier', 'supplier_product_id', 'name', 'category', 'price',
        'sales_count', 'rank', 'image_url', 'product_url', 'captured_on',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'captured_on' => 'date',
        ];
    }

    /**
     * Normaliza rank/sales_count a una escala 0-100 comparable con growthScore de Trends.
     */
    public function supplierSignalScore(): float
    {
        if ($this->rank) {
            // rank 1 (el mejor) -> 100 puntos, decae con la posición.
            return round(max(0, 100 - ($this->rank - 1)), 2);
        }

        if ($this->sales_count) {
            return round(min(100, log10($this->sales_count + 1) * 25), 2);
        }

        return 0.0;
    }
}
