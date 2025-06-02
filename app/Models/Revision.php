<?php

namespace App\Models;

use App\Enums\RevisionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Revision extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'turbineId',
        'revisionDate',
        'pdr_id',
        'performedBy',
        'status',
    ];

    protected $casts = [
        'revisionDate' => 'datetime',
        'status' => RevisionStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships from UML
    public function turbine(): BelongsTo
    {
        return $this->belongsTo(Turbine::class, 'turbineId');
    }

    public function pdr(): BelongsTo
    {
        return $this->belongsTo(PDR::class, 'pdr_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performedBy');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'revisionId');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'revisionId');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable'); // Assuming polymorphic
    }
} 