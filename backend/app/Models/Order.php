<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending',
        'confirmed',
        'processing',
        'shipped',
        'delivered',
        'cancelled',
    ];

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'full_name',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'payment_method',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
