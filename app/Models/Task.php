<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';
    // public $timestamps = false; // Changed - Timestamps are now enabled by default

    protected $fillable = [
        'revisionId',
        'description',
        'ordre',
        'status',
        'plannedAt',
        'doneAt',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'plannedAt' => 'datetime',
        'doneAt' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    // Relationships from UML
    public function revision(): BelongsTo
    {
        return $this->belongsTo(Revision::class, 'revisionId');
    }
} 