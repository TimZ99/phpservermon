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

            // The port must be a numeric value between 0 and 99999
            'port' => ['numeric', 'between:0,99999'],
        ];
    }
}
