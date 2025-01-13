<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Password;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateRequest extends FormRequest
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
            'current_password' => [
                'required',
                Password::min(8)
                    ->max(16)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
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
