<?php

declare(strict_types=1);

namespace Gaby\Shared\Domain;

final class Slug
{
    public static function fromText(string $text): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);

            if ($transliterated !== false) {
                $text = $transliterated;
            }
        }

        $slug = strtolower($text);
        $slug = (string) preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return substr($slug, 0, 140);
    }
}
