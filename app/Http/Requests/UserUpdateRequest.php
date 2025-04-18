<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * This will convert the suspended fields
     * to boolean values.
     */
    protected function prepareForValidation(): void
    {
        // Convert the suspended fields to boolean
        $this->merge([
            'suspended' => (bool) $this->input('suspended', false),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes', // Only validate if the field is present
                'required', // The field is required
                'string', // The field must be a string
                'max:255', // The field must not be longer than 255 characters
            ],
            'email' => [
                'sometimes', // Only validate if the field is present
                'required', // The field is required
                'string', // The field must be a string
                'lowercase', // The field must be lowercase
                'email', // The field must be a valid email
                'max:255', // The field must not be longer than 255 characters
                Rule::unique(User::class)->ignore($this->user), // The field must be unique
            ],
            'phone' => [
                'sometimes', // Only validate if the field is present
                'nullable', // The field is not required
                'string', // The field must be a string
                'max:20', // The field must not be longer than 20 characters
            ],
            'suspended' => [
                'required', // The field is required
                'boolean', // The field must be a boolean
            ],
            'scopes' => [
                'sometimes', // Only validate if the field is present
                'array', // The field must be an array
            ],
            'scopes.*' => [
                'sometimes', // Only validate if the field is present
                'string', // The field must be a string
                'in:'.implode(',', User::valid_scopes()), // The field must be one of the valid scopes
            ],
        ];
    }
}
