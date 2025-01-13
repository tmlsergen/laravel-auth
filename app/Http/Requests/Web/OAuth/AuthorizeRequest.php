<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\OAuth;

use Illuminate\Foundation\Http\FormRequest;

class AuthorizeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'string'],
            'redirect_uri' => ['required', 'url'],
            'scopes' => ['required', 'string'],
            'approve' => ['required', 'boolean'],
        ];
    }
}
