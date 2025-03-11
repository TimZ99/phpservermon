<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfigUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * This method returns an array of validation rules for the configuration update request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // The server name must be provided and should be a string with a maximum length of 255 characters
            'locale' => ['required', 'string', 'min:2', 'max:30'],
            'timezone' => ['required', 'timezone:all', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_address' => ['nullable', 'email:rcf,filter_unicode', 'max:255'],
            'telegram_bot_token' => ['nullable', 'string', 'min:20', 'max:50'],
        ];
    }
}
