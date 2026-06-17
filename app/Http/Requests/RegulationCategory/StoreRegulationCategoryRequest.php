<?php

namespace App\Http\Requests\RegulationCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegulationCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sub_categories' => ['nullable', 'array'],
            'sub_categories.*' => ['string', 'max:255'],
        ];
    }
}
