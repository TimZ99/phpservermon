<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;

/**
 * User Model
 *
 * This class represents the User model, which extends the Authenticatable class.
 * It includes traits for factory creation and notifications, and defines various
 * attributes, relationships, and utility methods for user management.
 *
 * Traits:
 * - HasFactory: Provides factory methods for creating model instances.
 * - Notifiable: Enables sending notifications to the user.
 *
 * Properties:
 * - $fillable: Specifies the attributes that can be mass-assigned.
 * - $hidden: Specifies the attributes that should be hidden during serialization.
 * - $casts: Defines the data type casting for specific attributes.
 *
 * Methods:
 * - isSuspended(): Checks if the user is suspended.
 * - servers(): Defines a many-to-many relationship with the Server model.
 * - isLastPowerfulUser(): Checks if the user is the last with user:edit:any scope.
 * - routeNotificationForTelegram(): Routes notifications to the user's Telegram account.
 * - hasScope(): Checks if the user has a specific scope.
 * - setScope(): Sets the scopes for the user.
 * - validScopes(): Returns a list of valid scopes.
 * - expand_scopes(): Expands the scopes based on implied relationships.
 *
 * @var suspended boolean
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'suspended',
        'telegram_user_id', 'scopes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended' => 'boolean',
            'telegram_user_id' => 'integer',
            'scopes' => 'json',
        ];
    }

    /**
     * Check if the user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->suspended === null ? false : $this->suspended;
    }

    /**
     * Get the servers the user is attached to.
     *
     * @return BelongsToMany<\App\Models\Server>
     */
    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class);
    }

    /**
     * Check if the user is the last powerful user.
     */
    public function isLastPowerfulUser(): bool
    {
        // check how many users have user:edit:any in there scopes
        $users_with_edit_user_scope = User::all()->filter(function ($user) {
            return $user->hasScope('user:edit:any');
        })->count();

        return $users_with_edit_user_scope <= 1 && $this->hasScope('user:edit:any');
    }

    /**
     * Route notifications for the telegram channel.
     */
    public function routeNotificationForTelegram(): int
    {
        return $this->telegram_user_id;
    }

    /**
     * Expand the scopes based on implied relationships.
     *
     * @param  string|array<string>  $scopes
     */
    public function hasScope(string|array $scope): bool
    {
        $scopes = $this->scopes ?? [];

        if (is_string($scopes)) {
            $scopes = json_decode($scopes, true) ?? [];
        }

        $scopes = is_array($scopes) ? $scopes : [];

        foreach ((array) $scope as $check) {
            if (in_array($check, $scopes, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Overrides the scopes that the user has.
     *
     * You need to run ->save() afterwards
     *
     * @var array<string>
     */
    public function setScope(array $scopes): void
    {
        $filtered = array_values(array_unique(array_filter(
            $scopes,
            fn ($scope) => self::isValidScope($scope)
        )));

        $this->scopes = $filtered;
    }

    /**
     * Add one scope to the user.
     *
     * You need to run ->save() afterwards
     *
     * @var string|array<string>
     *
     * @throws \InvalidArgumentException
     */
    public function addScope(string|array $scopes): void
    {
        foreach ((array) $scopes as $scope) {
            if (! $this->isValidScope($scope)) {
                throw new \InvalidArgumentException("Invalid scope: {$scope}");
            }

            if (! $this->hasScope($scope)) {
                $this->scopes[] = $scope;
            }
        }

        $this->scopes = array_values(array_unique($this->scopes));
    }

    /**
     * Remove one scope to the user.
     *
     * You need to run ->save() afterwards
     *
     * @var string|array<string>
     *
     * @throws \InvalidArgumentException
     */
    public function removeScope(string|array $scopes): void
    {
        foreach ((array) $scopes as $scope) {
            if (! $this->isValidScope($scope)) {
                throw new \InvalidArgumentException("Invalid scope: {$scope}");
            }

            if ($this->hasScope($scope)) {
                $this->scopes = array_filter($this->scopes, fn ($val) => $val !== $scope);
            }
        }

        $this->scopes = array_values(array_unique($this->scopes));
    }

    /**
     * List of valid scopes.
     *
     * @var list<string>
     */
    public static function validScopes(): array
    {
        return [
            'server:view:*',
            'server:manage:*',
            'server:check:*',
            'user:view:*',
            'user:manage:*',
            'config:manage',
        ];
    }

    public static function isValidScope(string $scope): bool
    {
        if (in_array($scope, self::validScopes(), true)) {
            return true;
        }

        [$model, $action, $target] = explode(':', $scope) + [null, null, null];

        return match (true) {
            // server:action:uuid
            $model === 'server' && in_array($action, ['view', 'manage', 'check']) => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $target),
            // user:action:id
            $model === 'user' && in_array($action, ['view', 'manage']) => ctype_digit($target),
            default => false,
        };
    }
}
