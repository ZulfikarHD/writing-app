<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddContextRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'context_type' => ['required', 'string', Rule::in(['scene', 'codex', 'summary', 'outline', 'custom'])],
            'reference_id' => ['nullable', 'integer'],
            'custom_content' => ['nullable', 'string', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'context_type.required' => 'Please specify the type of context.',
            'context_type.in' => 'Invalid context type. Must be one of: scene, codex, summary, outline, custom.',
            'custom_content.max' => 'Custom content is too long. Maximum 100,000 characters allowed.',
        ];
    }

    /**
     * Additional validation after rules pass.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('context_type');
            $referenceId = $this->input('reference_id');
            $customContent = $this->input('custom_content');

            // Scene and codex types require a reference_id
            if (in_array($type, ['scene', 'codex']) && ! $referenceId) {
                $validator->errors()->add('reference_id', "A reference ID is required for {$type} context type.");
            }

            // Custom type requires custom_content
            if ($type === 'custom' && empty($customContent)) {
                $validator->errors()->add('custom_content', 'Custom content is required for custom context type.');
            }
        });
    }
}
