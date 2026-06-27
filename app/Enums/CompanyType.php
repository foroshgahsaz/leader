<?php

namespace App\Enums;

enum CompanyType: string
{
    case Importer = 'importer';
    case Distributor = 'distributor';
    case Retailer = 'retailer';
    case Manufacturer = 'manufacturer';
    case Wholesaler = 'wholesaler';

    public function label(): string
    {
        return match ($this) {
            self::Importer => 'Importer',
            self::Distributor => 'Distributor',
            self::Retailer => 'Retailer',
            self::Manufacturer => 'Manufacturer',
            self::Wholesaler => 'Wholesaler',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
