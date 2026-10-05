<?php

namespace App\Enums;

enum ReportStatus: string
{
    case NEW = 'new';
    case RESOLVED = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'جديد',
            self::RESOLVED => 'تمت المعالجة',
        };
    }
}
