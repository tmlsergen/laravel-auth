<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $access_token
 * @property string $client_id
 * @property int|null $user_id
 * @property string $expires
 * @property string $scope
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken whereExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccessToken whereUserId($value)
 */
	class OAuthAccessToken extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $client_id
 * @property string $client_secret
 * @property string $redirect_uri
 * @property array<array-key, mixed> $grant_types
 * @property array<array-key, mixed> $scopes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereGrantTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereRedirectUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereScopes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereUpdatedAt($value)
 */
	class OAuthClient extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordHistory wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordHistory whereUserId($value)
 */
	class PasswordHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property string $id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $payload
 * @property int $last_activity
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereUserId($value)
 */
	class Session extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $two_factor_secret
 * @property bool $two_factor_enabled
 * @property string $two_factor_qr_path
 * @property string $two_factor_recovery_path
 * @property int $failed_login_attempts
 * @property string|null $locked_until
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property \Illuminate\Support\Carbon $password_changed_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PasswordHistory> $passwordHistories
 * @property-read int|null $password_histories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Session> $sessions
 * @property-read int|null $sessions_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFailedLoginAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLockedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePasswordChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorQrPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

