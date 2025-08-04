<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
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
        // Determine the user ID to ignore for unique email validation
        $ignoreId = $this->route('user')
                    ? $this->route('user')->id
                    : $this->user()->id;

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
                Rule::unique(User::class)->ignore($ignoreId), // The email must be unique, ignoring the current user's ID
            ],
            'phone' => [
                'sometimes', // Only validate if the field is present
                'nullable', // The field is not required
                'string', // The field must be a string
                'max:20', // The field must not be longer than 20 characters
            ],
            'telegram_user_id' => [
                'sometimes', // Only validate if the field is present
                'nullable', // The field is not required
                'integer', // The field must be an integer
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
                'in:'.implode(',', User::validScopes()), // The field must be one of the valid scopes
            ],
        ];
    }
}
