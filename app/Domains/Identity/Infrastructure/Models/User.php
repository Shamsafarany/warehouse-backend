<?php

namespace App\Domains\Identity\Infrastructure\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Domains\Identity\Domain\Enums\UserRole;
use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Order\Infrastructure\Models\Order;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;

#[UseFactory(UserFactory::class)]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUlids;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    //relations
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function getFullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
