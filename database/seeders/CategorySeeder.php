<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Support\ArabicSlug;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return [
            'مشويات',
            'مأكولات بحرية',
            'وجبات سريعة',
            'حلويات',
            'كافيهات',
            'مأكولات شعبية',
        ];
    }

    public function run(): void
    {
        foreach (self::names() as $name) {
            Category::firstOrCreate(
                ['slug' => ArabicSlug::make($name)],
                ['name_ar' => $name]
            );
        }
    }
}
