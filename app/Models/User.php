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
 * - is_last_powerful_user(): Checks if the user is the last with user:edit scope.
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
        // check how many users have user:edit in there scopes
        $users_with_edit_user_scope = User::all()->filter(function ($user) {
            return $user->has_scope('user:edit');
        })->count();

        return $users_with_edit_user_scope <= 1 && $this->has_scope('user:edit');
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
     * Set the scopes that the user has.
     *
     * @var list<string>
     */
    public function set_scopes(array $scopes): void
    {
        $valid = self::valid_scopes();

        $filtered = array_values(array_unique(array_filter($scopes, fn ($s) => in_array($s, $valid))));

        $this->scopes = $filtered;
        $this->save();
    }

    /**
     * List of valid scopes.
     *
     * @var list<string>
     */
    public static function valid_scopes(): array
    {
        return [
            // server
            'server:monitor', // monitor page
            'server:view', // index and view individual
            'server:create', // create
            'server:edit', // edit
            'server:delete', // delete
            'server:check', // run server checks
            // config
            'config:manage', // change global config
            // user
            'user:view', // index and view individual
            'user:create', // create
            'user:edit', // edit
            'user:delete', // delete
        ];
    }

    protected static function expand_scopes(array $scopes): array
    {
        // Apply implied scopes
        $scopes = collect($scopes);

        if ($scopes->intersect([
            'user:create',
            'user:edit',
            'user:delete',
        ])->isNotEmpty()) {
            $scopes = $scopes->merge(['user:view', 'user:create', 'user:edit', 'user:delete']);
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
