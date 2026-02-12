<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'status_id',
        'object_id',
        'object_name',
        'notification_from',
        'notification_to',
        'viewed',
        'message',
        'metadata',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notification_from');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notification_to');
    }
}
