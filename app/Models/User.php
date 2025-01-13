<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Kra8\Snowflake\HasSnowflakePrimary;

class User extends Authenticatable
{
    use HasFactory;
    use HasSnowflakePrimary;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_qr_path',
        'two_factor_recovery_path',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'two_factor_enabled' => 'boolean',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
