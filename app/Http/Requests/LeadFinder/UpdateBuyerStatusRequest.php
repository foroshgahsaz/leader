<?php

namespace App\Http\Requests\LeadFinder;

use App\Enums\BuyerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBuyerStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $buyer = $this->route('buyer');

        return $buyer && $this->user()?->can('update', $buyer);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(BuyerStatus::class)],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
