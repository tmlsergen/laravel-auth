<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kra8\Snowflake\HasSnowflakePrimary;

class OAuthAccessToken extends Model
{
    use HasSnowflakePrimary;

    protected $table = 'oauth_access_tokens';

    protected $fillable = [
        'access_token',
        'client_id',
        'user_id',
        'expires',
        'scope',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
