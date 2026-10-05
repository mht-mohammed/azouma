<?php

namespace Database\Seeders;

use App\Enums\OperatingStatus;
use App\Enums\RestaurantStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $owners = [
            User::where('email', 'owner1@azouma.local')->firstOrFail(),
            User::where('email', 'owner2@azouma.local')->firstOrFail(),
        ];

        // All names, phones and images below are FICTIONAL demo data.
        // Phones use the obviously-fake 00000X range; images are placeholders.
        $restaurants = [
            [
                'name' => 'مشاوي الدار التجريبي', 'category' => 'مشويات', 'area' => 'الرمال',
                'owner' => 0, 'status' => RestaurantStatus::APPROVED,
                'operating_status' => OperatingStatus::OPEN, 'verified' => true,
                'price_range' => 2, 'lat' => 31.5186000, 'lng' => 34.4500000, 'images' => 3,
            ],
            [
                'name' => 'بحر الشاطئ التجريبي', 'category' => 'مأكولات بحرية', 'area' => 'الشاطئ',
                'owner' => 1, 'status' => RestaurantStatus::APPROVED,
                'operating_status' => OperatingStatus::OPEN, 'verified' => true,
                'price_range' => 3, 'lat' => 31.5347000, 'lng' => 34.4385000, 'images' => 3,
            ],
            [
                'name' => 'برجر الزاوية التجريبي', 'category' => 'وجبات سريعة', 'area' => 'النصر',
                'owner' => 0, 'status' => RestaurantStatus::APPROVED,
                'operating_status' => OperatingStatus::OPEN, 'verified' => false,
                'price_range' => 1, 'lat' => 31.5220000, 'lng' => 34.4450000, 'images' => 2,
            ],
            [
                'name' => 'حلويات القمر التجريبي', 'category' => 'حلويات', 'area' => 'الدرج',
                'owner' => 1, 'status' => RestaurantStatus::APPROVED,
                'operating_status' => OperatingStatus::OPEN, 'verified' => true,
                'price_range' => 1, 'lat' => 31.5050000, 'lng' => 34.4660000, 'images' => 2,
            ],
            [
                'name' => 'قهوة الصباح التجريبي', 'category' => 'كافيهات', 'area' => 'تل الهوا',
                'owner' => 0, 'status' => RestaurantStatus::APPROVED,
                'operating_status' => OperatingStatus::OPEN, 'verified' => false,
                'price_range' => 2, 'lat' => 31.4950000, 'lng' => 34.4370000, 'images' => 2, 'closed_day' => 5,
            ],
            [
                'name' => 'فول وفلافل البلد التجريبي', 'category' => 'مأكولات شعبية', 'area' => 'الزيتون',
                'owner' => 1, 'status' => RestaurantStatus::APPROVED,
                'operating_status' => OperatingStatus::TEMPORARILY_CLOSED, 'verified' => false,
                'price_range' => 1, 'lat' => 31.4980000, 'lng' => 34.4620000, 'images' => 2,
            ],
            [
                'name' => 'شاورما النخيل التجريبي', 'category' => 'وجبات سريعة', 'area' => 'الشيخ رضوان',
                'owner' => 0, 'status' => RestaurantStatus::APPROVED,
                'operating_status' => OperatingStatus::RELOCATED, 'verified' => false,
                'price_range' => 1, 'lat' => 31.5300000, 'lng' => 34.4520000, 'images' => 3,
            ],
            [
                // Closes after midnight (overnight hours).
                'name' => 'كنافة الذهبية التجريبي', 'category' => 'حلويات', 'area' => 'التفاح',
                'owner' => 1, 'status' => RestaurantStatus::APPROVED,
                'operating_status' => OperatingStatus::OPEN, 'verified' => false,
                'price_range' => 2, 'lat' => 31.5120000, 'lng' => 34.4700000, 'images' => 2, 'overnight' => true,
            ],
            [
                'name' => 'مطبخ العائلة التجريبي', 'category' => 'مأكولات شعبية', 'area' => 'التفاح',
                'owner' => 0, 'status' => RestaurantStatus::APPROVED,
                'operating_status' => OperatingStatus::OPEN, 'verified' => false,
                'price_range' => 2, 'lat' => 31.5100000, 'lng' => 34.4680000, 'images' => 2, 'closed_day' => 6,
            ],
            [
                'name' => 'كافيه الكورنيش التجريبي', 'category' => 'كافيهات', 'area' => 'الشاطئ',
                'owner' => 1, 'status' => RestaurantStatus::PENDING,
                'operating_status' => OperatingStatus::OPEN, 'verified' => false,
                'price_range' => 2, 'lat' => 31.5360000, 'lng' => 34.4360000, 'images' => 2,
            ],
            [
                'name' => 'سمك الميناء التجريبي', 'category' => 'مأكولات بحرية', 'area' => 'الشاطئ',
                'owner' => 0, 'status' => RestaurantStatus::PENDING,
                'operating_status' => OperatingStatus::OPEN, 'verified' => false,
                'price_range' => 3, 'lat' => 31.5380000, 'lng' => 34.4340000, 'images' => 2,
            ],
            [
                'name' => 'بيتزا الفرن التجريبي', 'category' => 'وجبات سريعة', 'area' => 'الزيتون',
                'owner' => 1, 'status' => RestaurantStatus::REJECTED,
                'operating_status' => OperatingStatus::OPEN, 'verified' => false,
                'price_range' => 1, 'lat' => 31.5000000, 'lng' => 34.4600000, 'images' => 2,
                'rejection_reason' => 'العنوان غير واضح ورقم الهاتف ناقص - بيانات تجريبية',
            ],
        ];

        foreach ($restaurants as $index => $data) {
            if (Restaurant::where('name', $data['name'])->exists()) {
                continue;
            }

            DB::transaction(function () use ($data, $index, $owners) {
                $restaurant = Restaurant::create([
                    'owner_id' => $owners[$data['owner']]->id,
                    'category_id' => Category::where('name_ar', $data['category'])->firstOrFail()->id,
                    'area_id' => Area::where('name_ar', $data['area'])->firstOrFail()->id,
                    'name' => $data['name'],
                    'description' => 'وصف تجريبي لمطعم '.$data['name'].' — بيانات وهمية للعرض فقط.',
                    'phone' => '+970-59-00000'.str_pad((string) ($index + 10), 2, '0', STR_PAD_LEFT),
                    'whatsapp' => '+970-59-00000'.str_pad((string) ($index + 10), 2, '0', STR_PAD_LEFT),
                    'address' => 'غزة - '.$data['area'].' - شارع تجريبي '.($index + 1),
                    'price_range' => $data['price_range'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'status' => $data['status'],
                    'rejection_reason' => $data['rejection_reason'] ?? null,
                    'operating_status' => $data['operating_status'],
                    'operating_status_updated_at' => now(),
                    'is_verified' => $data['verified'],
                    'last_verified_at' => $data['verified'] ? now() : null,
                ]);

                for ($i = 0; $i < $data['images']; $i++) {
                    $restaurant->images()->create([
                        'path' => 'placeholders/restaurant-'.(($index % 12) + 1).'.jpg',
                        'is_cover' => $i === 0,
                        'sort_order' => $i,
                    ]);
                }

                // Weekday numbers: 0 = Saturday … 6 = Friday.
                for ($day = 0; $day <= 6; $day++) {
                    if (($data['closed_day'] ?? null) === $day) {
                        $restaurant->openingHours()->create([
                            'day_of_week' => $day,
                            'opens_at' => null,
                            'closes_at' => null,
                            'is_closed' => true,
                        ]);
                    } elseif (! empty($data['overnight'])) {
                        $restaurant->openingHours()->create([
                            'day_of_week' => $day,
                            'opens_at' => '18:00:00',
                            'closes_at' => '02:00:00',
                            'is_closed' => false,
                        ]);
                    } else {
                        $restaurant->openingHours()->create([
                            'day_of_week' => $day,
                            'opens_at' => '09:00:00',
                            'closes_at' => '23:00:00',
                            'is_closed' => false,
                        ]);
                    }
                }
            });
        }
    }
}
