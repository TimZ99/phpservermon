<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'event', 'channel', 'enabled'];

    /**
     * Get the user that owns the notification preference.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
