<?php

namespace App\Http\Requests;

use App\Models\Prompt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $prompt = $this->route('prompt');

        return $prompt && $prompt->canBeEditedBy($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['sometimes', 'string', Rule::in(Prompt::getTypes())],
            'system_message' => ['nullable', 'string'],
            'user_message' => ['nullable', 'string'],
            'model_settings' => ['nullable', 'array'],
            'model_settings.temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'model_settings.max_tokens' => ['nullable', 'integer', 'min:1'],
            'model_settings.top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'model_settings.frequency_penalty' => ['nullable', 'numeric', 'min:-2', 'max:2'],
            'model_settings.presence_penalty' => ['nullable', 'numeric', 'min:-2', 'max:2'],
            'folder_id' => ['nullable', 'integer', 'exists:prompt_folders,id'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
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
            'name.max' => 'Prompt name cannot exceed 255 characters.',
            'type.in' => 'Invalid prompt type selected.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ];
    }
}
