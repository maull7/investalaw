<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('review')->reviewer_id;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'summary' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'findings' => ['required', 'array'],
            'findings.*.regulation_id' => ['required', 'exists:regulations,id'],
            'findings.*.category_id' => ['nullable', 'exists:regulation_categories,id'],
            'findings.*.compliance_status' => ['required', 'in:compliant,partially_compliant,non_compliant'],
            'findings.*.findings' => ['nullable', 'string'],
            'findings.*.recommendations' => ['nullable', 'string'],
        ];
    }
}
