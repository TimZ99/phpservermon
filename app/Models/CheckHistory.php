<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CheckHistory extends Model
{
    use HasUuids;

    protected $table = 'check_history';

    protected $fillable = [
        'name',
        'server_id',
        'batch_id',
        'check_settings'
    ];

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
