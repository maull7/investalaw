<?php

namespace App\Http\Requests\RegulationType;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegulationTypeRequest extends FormRequest
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
            'level' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
