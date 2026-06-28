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
        return __('enums.company_type.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
