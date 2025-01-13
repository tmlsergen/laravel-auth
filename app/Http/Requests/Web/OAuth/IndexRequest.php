<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\OAuth;

use App\Enums\OAuthResponseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class IndexRequest extends FormRequest
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
            'response_type' => ['required', 'string', new Enum(OAuthResponseType::class)],
            'scope' => ['required', 'string'],
            'state' => ['nullable', 'string'],
        ];
    }
}
