<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\PasswordReset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->max(16)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }
}
