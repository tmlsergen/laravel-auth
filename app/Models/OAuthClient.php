<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OAuthClient extends Model
{
    use HasSnowflakePrimary;

    protected $table = 'oauth_clients';

    protected $fillable = [
        'client_id',
        'client_secret',
        'redirect_uri',
        'grant_types',
        'scope',
    ];

    protected function casts(): array
    {
        return [
            'grant_types' => 'array',
            'scopes' => 'array',
        ];
    }
}
