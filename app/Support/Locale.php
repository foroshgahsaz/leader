<?php

namespace App\Support;

class Locale
{
    public static function supported(): array
    {
        return config('locales.supported', ['en', 'fa']);
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::supported(), true);
    }

    public static function isRtl(?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        return (bool) config("locales.locales.{$locale}.rtl", false);
    }

    public static function direction(?string $locale = null): string
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }

    public static function fontUrl(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return config("locales.locales.{$locale}.font_url")
            ?? config('locales.locales.en.font_url');
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(config('locales.locales', []))
            ->only(self::supported())
            ->mapWithKeys(fn (array $meta, string $code): array => [$code => $meta['name']])
            ->all();
    }
}
