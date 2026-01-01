<?php

namespace App\Http\Requests;

use App\Models\CodexEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCodexEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $entry = $this->route('entry');

        return $entry && $entry->novel->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::in(CodexEntry::getTypes())],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'research_notes' => ['nullable', 'string'], // Sprint 13: US-12.3
            'thumbnail_path' => ['nullable', 'string', 'max:500'],
            'ai_context_mode' => ['sometimes', 'string', Rule::in(CodexEntry::getContextModes())],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_archived' => ['sometimes', 'boolean'],
            'is_tracking_enabled' => ['sometimes', 'boolean'], // Sprint 13: US-12.2
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Invalid entry type selected.',
            'name.max' => 'Entry name cannot exceed 255 characters.',
        ];
    }
}
