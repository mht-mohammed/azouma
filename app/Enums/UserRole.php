<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'مدير',
            self::OWNER => 'صاحب مطعم',
            self::CUSTOMER => 'عميل',
        };
    }
}
