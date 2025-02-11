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
     */
    protected function prepareForValidation(): void
    {
        // Convert the admin and suspended fields to boolean
        $this->merge([
            'admin' => (bool) $this->input('admin', false),
            'suspended' => (bool) $this->input('suspended', false)
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'admin' => ['required', 'boolean'],
            'suspended' => ['required', 'boolean'],
        ];
    }
}
