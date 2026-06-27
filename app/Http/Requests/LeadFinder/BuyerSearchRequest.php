<?php

namespace App\Http\Requests\LeadFinder;

use App\Enums\BuyerStatus;
use App\Enums\CompanyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuyerSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('search', \App\Models\Buyer::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'product' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'size:2'],
            'industry' => ['nullable', 'string', 'max:150'],
            'company_type' => ['nullable', Rule::enum(CompanyType::class)],
            'company_types' => ['nullable', 'array'],
            'company_types.*' => [Rule::enum(CompanyType::class)],
            'min_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_score' => ['nullable', 'integer', 'min:0', 'max:100', 'gte:min_score'],
            'query' => ['nullable', 'string', 'max:255'],
            'is_favorite' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::enum(BuyerStatus::class)],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'sort_by' => ['nullable', 'string', Rule::in(['name', 'country_code', 'import_activity_level', 'score'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ];
    }
}
