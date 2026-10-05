<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $urls = Cache::remember('sitemap', 3600, function () {
            $entries = [
                ['loc' => route('home'), 'lastmod' => now()->toDateString()],
            ];

            foreach (Restaurant::approved()->select(['slug', 'updated_at'])->get() as $restaurant) {
                $entries[] = [
                    'loc' => route('restaurants.show', $restaurant),
                    'lastmod' => $restaurant->updated_at->toDateString(),
                ];
            }

            return $entries;
        });

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'text/xml');
    }

    public function robots(): Response
    {
        return response(
            "User-agent: *\n".
            "Disallow: /admin/\n".
            "Disallow: /owner/\n".
            "Disallow: /dashboard\n".
            "Disallow: /profile\n".
            'Sitemap: '.url('/sitemap.xml')."\n",
            200,
            ['Content-Type' => 'text/plain']
        );
    }
}
