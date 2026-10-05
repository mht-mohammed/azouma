<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Support\ArabicSlug;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Real Gaza City neighborhood names. Edit freely as areas change.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return [
            'الرمال',
            'الشاطئ',
            'النصر',
            'الشيخ رضوان',
            'التفاح',
            'الدرج',
            'الزيتون',
            'تل الهوا',
        ];
    }

    public function run(): void
    {
        foreach (self::names() as $name) {
            Area::firstOrCreate(
                ['slug' => ArabicSlug::make($name)],
                ['name_ar' => $name]
            );
        }
    }
}
