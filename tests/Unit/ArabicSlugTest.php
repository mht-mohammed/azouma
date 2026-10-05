<?php

namespace Tests\Unit;

use App\Support\ArabicSlug;
use PHPUnit\Framework\TestCase;

class ArabicSlugTest extends TestCase
{
    public function test_keeps_arabic_letters_with_hyphens(): void
    {
        $this->assertSame('مطعم-الشاطئ', ArabicSlug::make('مطعم الشاطئ'));
    }

    public function test_keeps_mixed_arabic_latin_and_digits(): void
    {
        $this->assertSame('مطعم-burger-12', ArabicSlug::make('مطعم Burger 12'));
    }

    public function test_strips_diacritics_and_tatweel(): void
    {
        $withDiacritics = ArabicSlug::make('مَطْعَمُ الشَّاطِئ ـ');
        $plain = ArabicSlug::make('مطعم الشاطئ');

        $this->assertSame($plain, $withDiacritics);
    }

    public function test_strips_punctuation_and_collapses_spaces(): void
    {
        $this->assertSame('مطعم-الشاطئ', ArabicSlug::make('  مطعم!!!   الشاطئ؟؟  '));
    }

    public function test_returns_empty_string_when_nothing_usable_remains(): void
    {
        // The Restaurant model turns this into a "restaurant-xxxxxx" fallback.
        $this->assertSame('', ArabicSlug::make('!!! ??? ...'));
    }

    public function test_limits_length_to_eighty_characters(): void
    {
        $slug = ArabicSlug::make(str_repeat('مطعم ', 40));

        $this->assertLessThanOrEqual(80, mb_strlen($slug, 'UTF-8'));
        $this->assertStringNotContainsString(' ', $slug);
    }
}
