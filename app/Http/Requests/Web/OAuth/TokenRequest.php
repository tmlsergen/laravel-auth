<?php

namespace App\Http\Requests\Web\OAuth;

use App\Enums\OAuthGrantType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class TokenRequest extends FormRequest
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
            'grant_type' => ['required', 'string', new Enum(OAuthGrantType::class)],
            'code' => ['required_if:grant_type,authorization_code', 'string'],
            'refresh_token' => ['required_if:grant_type,refresh_token', 'string'],
            'redirect_uri' => ['required', 'url'],
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
            'scope' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw (new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422)));
    }
}
