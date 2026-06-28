<?php

namespace App\Support;

class CountryName
{
    /**
     * @var array<string, string>
     */
    protected static array $map = [
        'TR' => 'Turkey',
        'DE' => 'Germany',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'NL' => 'Netherlands',
        'BE' => 'Belgium',
        'AE' => 'United Arab Emirates',
        'SA' => 'Saudi Arabia',
        'IQ' => 'Iraq',
        'IR' => 'Iran',
        'IN' => 'India',
        'CN' => 'China',
        'JP' => 'Japan',
        'KR' => 'South Korea',
        'RU' => 'Russia',
        'PL' => 'Poland',
        'EG' => 'Egypt',
        'QA' => 'Qatar',
        'KW' => 'Kuwait',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'AF' => 'Afghanistan',
        'AZ' => 'Azerbaijan',
        'GE' => 'Georgia',
        'AM' => 'Armenia',
    ];

    public static function fromCode(string $countryCode): string
    {
        $code = strtoupper(trim($countryCode));

        return self::$map[$code] ?? $code;
    }

    /**
     * @param  list<string>  $countryCodes
     * @return list<string>
     */
    public static function fromCodes(array $countryCodes): array
    {
        return array_values(array_unique(array_map(
            fn (string $code): string => self::fromCode($code),
            $countryCodes,
        )));
    }

    public static function toCode(?string $countryName): ?string
    {
        if ($countryName === null || $countryName === '') {
            return null;
        }

        if (strlen($countryName) === 2) {
            return strtoupper($countryName);
        }

        $normalized = strtolower(trim($countryName));

        foreach (self::$map as $code => $name) {
            if (strtolower($name) === $normalized) {
                return $code;
            }
        }

        return null;
    }
}
