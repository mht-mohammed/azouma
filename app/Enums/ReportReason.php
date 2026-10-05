<?php

namespace App\Enums;

enum ReportReason: string
{
    case WRONG_INFO = 'wrong_info';
    case CLOSED = 'closed';
    case MOVED = 'moved';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WRONG_INFO => 'معلومات خاطئة',
            self::CLOSED => 'المطعم مغلق',
            self::MOVED => 'المطعم انتقل',
            self::OTHER => 'أخرى',
        };
    }
}
