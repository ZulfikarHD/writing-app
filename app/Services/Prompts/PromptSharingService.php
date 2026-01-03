<?php

namespace App\Services\Prompts;

use App\Models\Prompt;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PromptSharingService
{
    protected const VERSION = '1.0';

    protected const APP_IDENTIFIER = 'writing-app';

    public function __construct(
        protected PromptService $promptService
    ) {}

    /**
     * Export a prompt to a shareable compressed string.
     */
    public function exportPrompt(Prompt $prompt): string
    {
        // Load relationships
        $prompt->load('inputs');

        $data = [
            '_version' => self::VERSION,
            '_app' => self::APP_IDENTIFIER,
            '_exported_at' => now()->toISOString(),
            'name' => $prompt->name,
            'description' => $prompt->description,
            'type' => $prompt->type,
            'system_message' => $prompt->system_message,
            'user_message' => $prompt->user_message,
            'messages' => $prompt->messages,
            'model_settings' => $prompt->model_settings,
            'inputs' => $prompt->inputs->map(fn ($input) => [
                'name' => $input->name,
                'label' => $input->label,
                'type' => $input->type,
                'options' => $input->options,
                'default_value' => $input->default_value,
                'placeholder' => $input->placeholder,
                'description' => $input->description,
                'is_required' => $input->is_required,
                'sort_order' => $input->sort_order,
            ])->toArray(),
        ];

        // Compress and encode
        $json = json_encode($data);
        $compressed = gzcompress($json, 9);

        return base64_encode($compressed);
    }

    /**
     * Preview an encoded prompt without creating it.
     *
     * @return array<string, mixed>
     */
    public function previewImport(string $encoded): array
    {
        $data = $this->decodePromptData($encoded);

        return [
            'name' => $data['name'] ?? 'Unnamed Prompt',
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'chat',
            'has_system_message' => ! empty($data['system_message']),
            'has_user_message' => ! empty($data['user_message']),
            'has_messages' => ! empty($data['messages']),
            'has_model_settings' => ! empty($data['model_settings']),
            'input_count' => count($data['inputs'] ?? []),
            'version' => $data['_version'] ?? 'unknown',
            'app' => $data['_app'] ?? 'unknown',
            'exported_at' => $data['_exported_at'] ?? null,
        ];
    }

    /**
     * Import a prompt from an encoded string.
     */
    public function importPrompt(string $encoded, User $user): Prompt
    {
        $data = $this->decodePromptData($encoded);

        // Validate the prompt data
        $this->validatePromptData($data);

        // Create the prompt
        $promptData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'system_message' => $data['system_message'] ?? null,
            'user_message' => $data['user_message'] ?? null,
            'messages' => $data['messages'] ?? null,
            'model_settings' => $data['model_settings'] ?? null,
        ];

        $prompt = $this->promptService->createPrompt($user, $promptData);

        // Create inputs if any
        if (! empty($data['inputs'])) {
            foreach ($data['inputs'] as $inputData) {
                $prompt->inputs()->create([
                    'name' => $inputData['name'],
                    'label' => $inputData['label'],
                    'type' => $inputData['type'] ?? 'text',
                    'options' => $inputData['options'] ?? null,
                    'default_value' => $inputData['default_value'] ?? null,
                    'placeholder' => $inputData['placeholder'] ?? null,
                    'description' => $inputData['description'] ?? null,
                    'is_required' => $inputData['is_required'] ?? false,
                    'sort_order' => $inputData['sort_order'] ?? 0,
                ]);
            }
        }

        return $prompt->fresh()->load('inputs');
    }

    /**
     * Decode and decompress the encoded prompt data.
     *
     * @return array<string, mixed>
     *
     * @throws \InvalidArgumentException
     */
    protected function decodePromptData(string $encoded): array
    {
        // Trim whitespace
        $encoded = trim($encoded);

        if (empty($encoded)) {
            throw new \InvalidArgumentException('No prompt data provided.');
        }

        // Decode base64
        $compressed = base64_decode($encoded, true);
        if ($compressed === false) {
            throw new \InvalidArgumentException('Invalid prompt format. Please ensure you copied the entire prompt string.');
        }

        // Decompress
        $json = @gzuncompress($compressed);
        if ($json === false) {
            throw new \InvalidArgumentException('Invalid prompt format. The data appears to be corrupted.');
        }

        // Parse JSON
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid prompt format. Could not parse prompt data.');
        }

        return $data;
    }

    /**
     * Validate the prompt data structure.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    protected function validatePromptData(array $data): void
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:chat,prose,replacement,summary'],
            'system_message' => ['nullable', 'string'],
            'user_message' => ['nullable', 'string'],
            'messages' => ['nullable', 'array'],
            'model_settings' => ['nullable', 'array'],
            'inputs' => ['nullable', 'array'],
            'inputs.*.name' => ['required_with:inputs', 'string', 'max:100'],
            'inputs.*.label' => ['required_with:inputs', 'string', 'max:255'],
            'inputs.*.type' => ['required_with:inputs', 'string', 'in:text,textarea,select,number,checkbox'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
