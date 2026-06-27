<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\GlobalBuyerRepositoryInterface;
use App\Data\LeadFinder\ImportLeadRowData;
use App\Enums\CompanyType;
use App\Models\GlobalBuyer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GlobalBuyerRepository implements GlobalBuyerRepositoryInterface
{
    public function find(string $id): ?GlobalBuyer
    {
        return GlobalBuyer::query()->find($id);
    }

    public function findByProviderKey(string $providerKey): ?GlobalBuyer
    {
        return GlobalBuyer::query()
            ->where('provider_key', $providerKey)
            ->first();
    }

    public function findMany(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return GlobalBuyer::query()
            ->whereIn('id', $ids)
            ->get();
    }

    public function create(array $attributes): GlobalBuyer
    {
        return GlobalBuyer::query()->create($attributes);
    }

    public function update(string $id, array $attributes): GlobalBuyer
    {
        $globalBuyer = GlobalBuyer::query()->findOrFail($id);

        $globalBuyer->fill($attributes)->save();

        return $globalBuyer->fresh();
    }

    public function upsertFromImport(ImportLeadRowData $data, string $providerKey): GlobalBuyer
    {
        $companyType = $this->resolveCompanyType($data->companyType);

        return GlobalBuyer::query()->updateOrCreate(
            ['provider_key' => $providerKey],
            [
                'legal_name' => $data->name,
                'display_name' => $data->name,
                'country_code' => strtoupper($data->countryCode),
                'city' => $data->city,
                'website' => $data->website,
                'industry' => $data->industry,
                'company_type' => $companyType,
                'import_activity_level' => 0,
                'data_freshness_at' => now(),
                'firmographics' => array_filter([
                    'email' => $data->email,
                    'phone' => $data->phone,
                ]),
            ],
        );
    }

    public function delete(string $id): bool
    {
        $globalBuyer = GlobalBuyer::query()->find($id);

        if (! $globalBuyer) {
            return false;
        }

        return (bool) $globalBuyer->delete();
    }

    public static function providerKeyFromImportRow(ImportLeadRowData $data): string
    {
        $normalized = Str::lower(trim($data->name)).':'.strtoupper(trim($data->countryCode));

        return 'import_'.hash('sha256', $normalized);
    }

    protected function resolveCompanyType(?string $companyType): ?CompanyType
    {
        if ($companyType === null || $companyType === '') {
            return null;
        }

        $normalized = Str::lower(str_replace(' ', '_', trim($companyType)));

        return CompanyType::tryFrom($normalized);
    }
}
