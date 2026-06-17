<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isReviewer() || $this->user()->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'review_document_id' => ['required', 'exists:review_documents,id'],
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
