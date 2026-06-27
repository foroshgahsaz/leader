<?php

namespace App\Models;

use App\Enums\CompanyType;
use App\Models\Concerns\HasUuidPrimaryKey;
use Database\Factories\GlobalBuyerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlobalBuyer extends Model
{
    /** @use HasFactory<GlobalBuyerFactory> */
    use HasFactory;

    use HasUuidPrimaryKey;

    protected $fillable = [
        'provider_key',
        'legal_name',
        'display_name',
        'country_code',
        'city',
        'website',
        'industry',
        'company_type',
        'employee_range',
        'firmographics',
        'import_profile',
        'import_activity_level',
        'data_freshness_at',
    ];

    protected function casts(): array
    {
        return [
            'company_type' => CompanyType::class,
            'firmographics' => 'array',
            'import_profile' => 'array',
            'import_activity_level' => 'integer',
            'data_freshness_at' => 'datetime',
        ];
    }

    protected static function newFactory(): GlobalBuyerFactory
    {
        return GlobalBuyerFactory::new();
    }

    public function buyers(): HasMany
    {
        return $this->hasMany(Buyer::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(BuyerScore::class);
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(BuyerSummary::class);
    }
}
