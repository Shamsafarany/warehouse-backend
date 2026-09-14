<?php

namespace App\Domains\Order\Infrastructure\Models;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Payment\Infrastructure\Models\Payment;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(OrderFactory::class)]
class Order extends Model
{
    use HasUlids, SoftDeletes,  HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'shipping_address',
        'billing_address',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
