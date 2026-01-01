<?php

namespace App\Http\Requests;

use App\Models\AIConnection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAIConnectionRequest extends FormRequest
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
        $providers = array_keys(AIConnection::getProviders());

        return [
            'provider' => ['required', Rule::in($providers)],
            'name' => ['required', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'base_url' => ['nullable', 'url', 'max:500'],
            'settings' => ['nullable', 'array'],
            'is_default' => ['nullable', 'boolean'],
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
            'provider.required' => 'Please select an AI provider.',
            'provider.in' => 'The selected provider is not supported.',
            'name.required' => 'Please enter a name for this connection.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'base_url.url' => 'Please enter a valid URL.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $provider = $this->input('provider');
            $apiKey = $this->input('api_key');
            $providers = AIConnection::getProviders();

            if ($provider && isset($providers[$provider]) && ($providers[$provider]['requires_api_key'] ?? false)) {
                if (empty($apiKey)) {
                    $validator->errors()->add('api_key', 'An API key is required for this provider.');
                }
            }
        });
    }
}
