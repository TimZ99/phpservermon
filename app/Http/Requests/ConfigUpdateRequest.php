<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfigUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email_from_name' => $this->filled('email_from_name') ? $this->email_from_name : null,
            'email_from_address' => $this->filled('email_from_address') ? $this->email_from_address : null,
            'email_host' => $this->filled('email_host') ? $this->email_host : null,
            'email_port' => $this->filled('email_port') ? (int) $this->email_port : null,
            'email_username' => $this->filled('email_username') ? $this->email_username : null,
            'email_password' => $this->filled('email_password') ? $this->email_password : null,
            'email_encryption' => $this->filled('email_encryption') ? $this->email_encryption : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     * This method returns an array of validation rules for the configuration update request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'min:2', 'max:30'],
            'timezone' => ['required', 'timezone:all', 'max:255'],
            'check_history_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'email_global_enabled' => ['boolean'],
            'email_from_name' => ['nullable', 'string', 'max:255'],
            'email_from_address' => ['nullable', 'email:rfc,filter_unicode', 'max:255'],
            'email_host' => ['nullable', 'string', 'max:255'],
            'email_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'email_username' => ['nullable', 'string', 'max:255'],
            'email_password' => ['nullable', 'string', 'max:255'],
            'email_encryption' => ['nullable', 'in:ssl,tls'],
            'telegram_global_enabled' => ['boolean'],
            'telegram_bot_token' => ['nullable', 'string', 'min:20', 'max:50'],
        ];
    }
}
