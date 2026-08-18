<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PAID = 'paid';
    public const STATUS_PAYMENT_FAILED = 'payment_failed';
    public const STATUS_SUBMITTED_TO_SUPPLIER = 'submitted_to_supplier';
    public const STATUS_SUBMISSION_FAILED = 'submission_failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_number', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address', 'status', 'subtotal', 'shipping_cost', 'total',
        'currency', 'mp_preference_id', 'mp_payment_id', 'cj_order_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
