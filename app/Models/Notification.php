<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'recipientId',
        'type',
        'message',
        'seen',
        'entity_id',
        'entity_type',
    ];

    protected $casts = [
        'seen' => 'boolean',
        'createdAt' => 'datetime',
        // No updatedAt based on UML, but often included by default
        // 'updatedAt' => 'datetime',
    ];

    // If using Laravel's built-in notifications, this model might differ.
    // Assuming a custom implementation based purely on the UML.

    // Relationships from UML
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipientId');
    }

    /**
     * Get the related entity (e.g., the commented PDR, the assigned Planning).
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
} 