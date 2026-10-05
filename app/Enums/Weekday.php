<?php

namespace App\Enums;

enum Weekday: int
{
    case SATURDAY = 0;
    case SUNDAY = 1;
    case MONDAY = 2;
    case TUESDAY = 3;
    case WEDNESDAY = 4;
    case THURSDAY = 5;
    case FRIDAY = 6;

    public function label(): string
    {
        return match ($this) {
            self::SATURDAY => 'السبت',
            self::SUNDAY => 'الأحد',
            self::MONDAY => 'الاثنين',
            self::TUESDAY => 'الثلاثاء',
            self::WEDNESDAY => 'الأربعاء',
            self::THURSDAY => 'الخميس',
            self::FRIDAY => 'الجمعة',
        };
    }
}
