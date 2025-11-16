<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServerUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * This method returns an array of validation rules for the server update request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // The server name must be provided and should be a string with a maximum length of 255 characters
            'name' => ['required', 'string', 'max:255'],

            // The IP address is optional but must be a string with a maximum length of 255 characters if present
            'ip' => ['string', 'max:255'],

            // The port must be a numeric value between 0 and 99999, or null
            'port' => ['nullable', 'numeric', 'between:0,99999'],

            'check_settings' => ['nullable', 'array'],
            'check_settings.*.enabled' => ['nullable', 'boolean'],
            'check_settings.SSL_expiration.days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'check_settings.ContentRegex.pattern' => ['nullable', 'string', 'max:2000'],
            'check_settings.Latency.warning_ms' => ['nullable', 'integer', 'min:10', 'max:120000'],
            'check_settings.Latency.fail_ms' => ['nullable', 'integer', 'min:10', 'max:120000'],
            'check_settings.Headers.required' => ['nullable', 'string', 'max:5000'],
            'users' => ['nullable', 'array'],
            'users.*' => ['integer', 'exists:users,id'],
        ];
    }
}
