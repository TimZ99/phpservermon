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
 * - is_suspended(): Checks if the user is suspended.
 * - servers(): Defines a many-to-many relationship with the Server model.
 * - is_last_powerful_user(): Checks if the user is the last with user:edit:all scope.
 * - routeNotificationForTelegram(): Routes notifications to the user's Telegram account.
 * - has_scope(): Checks if the user has a specific scope.
 * - set_scopes(): Sets the scopes for the user.
 * - valid_scopes(): Returns a list of valid scopes.
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
        'name',
        'email',
        'phone',
        'password',
        'suspended',
        'telegram_user_id',
        'scopes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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

    public static function booted()
    {
        static::saving(function (self $user) {
            $user->scopes = self::expand_scopes($user->scopes ?? []);
        });
    }

    /**
     * Check if the user is suspended.
     */
    public function is_suspended(): bool
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
    public function is_last_powerful_user(): bool
    {
        // check how many users have user:edit:all in there scopes
        $users_with_edit_user_scope = User::all()->filter(function ($user) {
            return $user->has_scope('user:edit:all');
        })->count();

        return $users_with_edit_user_scope <= 1 && $this->has_scope('user:edit:all');
    }

    /**
     * Route notifications for the telegram channel.
     */
    public function routeNotificationForTelegram(): int
    {
        return $this->telegram_user_id;
    }

    /**
     * The scopes that the user has.
     *
     * @var list<string>
     */
    public function has_scope(string $scope): bool
    {
        $scopes = $this->scopes ?? [];

        if (is_string($scopes)) {
            $scopes = json_decode($scopes, true);
        }

        return in_array($scope, $scopes ?? []);
    }

    /**
     * Overrides the scopes that the user has.
     * 
     * You need to run ->save() afterwards
     *
     * @var list<string>
     */
    public function set_scopes(array $scopes): void
    {
        $filtered = array_values(array_unique(array_filter(
            $scopes,
            fn ($scope) => self::is_valid_scope($scope)
        )));

        $this->scopes = $filtered;
    }

    /**
     * Add one scope to the user.
     * 
     * You need to run ->save() afterwards
     *
     * @var list<string>
     */
    public function add_scope(string $scope): void
    {
        // Check if the scope is valid
        // If not, we don't need to do anything
        if($this->is_valid_scope($scope) === false) {
            throw new \InvalidArgumentException("Invalid scope: {$scope}");
        }

        // Check if the user has the scope
        // If so, we don't need to do anything
        if ($this->has_scope($scope)) {
            return;
        }

        // Add the scope to the list and return the new list
        array_push($this->scopes, $scope);
    }

    /**
     * Remove one scope to the user.
     * 
     * You need to run ->save() afterwards
     *
     * @var list<string>
     */
    public function remove_scope(string $scope): void
    {
        // Check if the scope is valid
        // If not, we don't need to do anything
        if($this->is_valid_scope($scope) === false) {
            throw new \InvalidArgumentException("Invalid scope: {$scope}");
        }

        // Check if the user has the scope
        // If not, we don't need to do anything
        if (!$this->has_scope($scope)) {
            return;
        }

        // Remove the scope from the list and return the new list
        $this->scopes = array_values(array_unique(array_filter($this->scopes, function ($value) use ($scope) {
            return $value !== $scope;
        })));
    }

    /**
     * List of valid scopes.
     *
     * @var list<string>
     */
    public static function valid_scopes(): array
    {
        return [
            // servers
            'server:index',
            'server:create',
            'server:monitor',
            // users
            'user:index',
            'user:view:all',
            'user:edit:all',
            'user:create',
            'user:delete:all',
            // config
            'config:manage',
        ];
    }

    public static function is_valid_scope(string $scope): bool
    {
        if (in_array($scope, self::valid_scopes(), true)) {
            return true;
        }

        // server-specific scopes: server:{uuid}:view|edit|delete
        return (bool) preg_match(
            '/^server:[0-9a-fA-F\-]{36}:(view|edit|delete)$/',
            $scope
        );
    }

    // @todo: remove this method
    protected static function expand_scopes(array $scopes): array
    {
        return $scopes;

        // Apply implied scopes
        $scopes = collect($scopes);

        if ($scopes->intersect([
            'user:create',
            'user:edit:all',
            'user:delete',
        ])->isNotEmpty()) {
            $scopes = $scopes->merge(['user:view:all', 'user:create', 'user:edit:all', 'user:delete']);
        }

        if ($scopes->intersect([
            'server:create',
            'server:edit',
            'server:delete',
        ])->isNotEmpty()) {
            $scopes = $scopes->merge(['server:view', 'server:create', 'server:edit', 'server:delete']);
        }

        return $scopes->flatten()->unique()->values()->all();
    }
}
