<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNovelRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'genre' => ['nullable', 'string', 'max:50'],
            'pov' => ['nullable', Rule::in(['first_person', 'third_person_limited', 'third_person_omniscient', 'second_person'])],
            'tense' => ['nullable', Rule::in(['past', 'present'])],
            'pen_name_id' => ['nullable', 'exists:pen_names,id'],
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
            'title.required' => 'Please enter a title for your novel.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'pen_name_id.exists' => 'The selected pen name is invalid.',
        ];
    }
}
