<?php

namespace App\Enums;

enum OperatingStatus: string
{
    case OPEN = 'open';
    case TEMPORARILY_CLOSED = 'temporarily_closed';
    case RELOCATED = 'relocated';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'مفتوح',
            self::TEMPORARILY_CLOSED => 'مغلق مؤقتاً',
            self::RELOCATED => 'انتقل إلى موقع جديد',
        };
    }
}
