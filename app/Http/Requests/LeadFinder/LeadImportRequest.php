<?php

namespace App\Http\Requests\LeadFinder;

use Illuminate\Foundation\Http\FormRequest;

class LeadImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('import', \App\Models\Buyer::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }
}
