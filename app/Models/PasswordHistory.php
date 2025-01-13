<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kra8\Snowflake\HasSnowflakePrimary;

class PasswordHistory extends Model
{
    use HasSnowflakePrimary;

    protected $table = 'password_histories';

    protected $fillable = [
        'user_id',
        'password',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
