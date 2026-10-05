<?php

namespace App\Support;

use Illuminate\Support\Str;

class ArabicSlug
{
    /**
     * Build a URL-safe slug that keeps Arabic letters, Latin letters and digits.
     *
     * Removes Arabic diacritics (tashkeel) and tatweel (kashida), strips
     * punctuation and symbols, replaces whitespace with a single hyphen.
     * Returns an empty string when nothing usable remains (the caller
     * decides on a fallback), so uniqueness suffixes stay predictable.
     */
    public static function make(string $value, int $limit = 80): string
    {
        // Remove tatweel (U+0640) and diacritics: tashkeel U+064B–U+0652 + superscript alef U+0670.
        $value = (string) preg_replace('/[\x{0640}\x{064B}-\x{0652}\x{0670}]/u', '', $value);

        $value = mb_strtolower($value, 'UTF-8');

        // Keep Arabic letters (not Arabic punctuation like ؟ ، ؛), Latin
        // letters, digits (ASCII + Arabic-Indic), spaces, hyphens, underscores.
        $value = (string) preg_replace('/[^a-z0-9\x{0621}-\x{064A}\x{0660}-\x{0669}\x{0671}-\x{06D3}\x{06F0}-\x{06F9}\s_-]/u', '', $value);

        // Whitespace/underscores become one hyphen; collapse repeats; trim edges.
        $value = (string) preg_replace('/[\s_]+/u', '-', $value);
        $value = (string) preg_replace('/-+/u', '-', $value);
        $value = trim($value, '-');

        if (mb_strlen($value, 'UTF-8') > $limit) {
            $value = rtrim(mb_substr($value, 0, $limit, 'UTF-8'), '-');
        }

        return $value;
    }

    /**
     * Build a unique slug, appending -2, -3… while $exists returns true.
     *
     * @param  callable(string): bool  $exists
     */
    public static function uniqueSlug(string $name, callable $exists, int $limit = 80, string $fallbackPrefix = 'item-'): string
    {
        $base = static::make($name, $limit);

        if ($base === '') {
            $base = $fallbackPrefix.Str::lower(Str::random(6));
        }

        $slug = $base;
        $counter = 2;

        while ($exists($slug)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
