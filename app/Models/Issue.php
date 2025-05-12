<?php

namespace App\Models;

use App\Enums\Severity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Issue extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'revisionId',
        'description',
        'severity',
        'reportedAt',
    ];

    protected $casts = [
        'severity' => Severity::class,
        'reportedAt' => 'datetime',
        'createdAt' => 'datetime', // Assuming standard timestamps
        'updatedAt' => 'datetime',
    ];

    // Relationships from UML
    public function revision(): BelongsTo
    {
        return $this->belongsTo(Revision::class, 'revisionId');
    }
} 