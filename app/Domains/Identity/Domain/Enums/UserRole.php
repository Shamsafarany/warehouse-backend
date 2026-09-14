<?php

namespace App\Domains\Identity\Domain\Enums;

enum UserRole: string
{
    case CUSTOMER = 'customer';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match($this) {
            self::CUSTOMER => 'Customer',
            self::ADMIN => 'Administrator',
        };
    }
}