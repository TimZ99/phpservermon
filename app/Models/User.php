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
 * - isAdmin(): Checks if the user has admin privileges.
 * - isSuspended(): Checks if the user is suspended.
 * - servers(): Defines a many-to-many relationship with the Server model.
 * - isLastAdmin(): Checks if the user is the last admin in the system.
 * - routeNotificationForTelegram(): Routes notifications to the user's Telegram account.
 *
 * @var admin boolean
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
        'admin',
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
            'admin' => 'boolean',
            'suspended' => 'boolean',
            'telegram_user_id' => 'integer',
            'scopes' => 'json',
        ];
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->admin === null ? false : $this->admin;
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
     * Check if the user is the last admin.
     */
    public function isLastAdmin(): bool
    {
        return User::where('admin', true)->count() <= 1 && $this->admin;
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
    public function hasScope(string $scope): bool
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
    public function setScopes(array $scopes): void
    {
        $valid = self::validScopes();

        $filtered = array_values(array_unique(array_filter($scopes, fn ($s) => in_array($s, $valid))));

        $this->scopes = $filtered;
        $this->save();
    }

    /**
     * List of valid scopes.
     *
     * @var list<string>
     */
    public static function validScopes(): array
    {
        return [
            // server
            'read:server',
            'create:server',
            'edit:server',
            'delete:server',
            // config
            'manage:config',
            // user
            'read:user',
            'create:user',
            'edit:user',
            'delete:user',
        ];
    }
}
