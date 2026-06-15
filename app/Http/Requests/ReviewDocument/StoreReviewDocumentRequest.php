<?php

namespace App\Http\Requests\ReviewDocument;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['exists:regulation_categories,id'],
        ];
    }
}
