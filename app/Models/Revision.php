<?php

namespace App\Models;

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
        'linkedPdrId',
        'performedBy',
    ];

    protected $casts = [
        'revisionDate' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    // Relationships from UML
    public function turbine(): BelongsTo
    {
        return $this->belongsTo(Turbine::class, 'turbineId');
    }

    public function linkedPdr(): BelongsTo // Relation 'génère' (inverse)
    {
        return $this->belongsTo(PDR::class, 'linkedPdrId');
    }

    public function performer(): BelongsTo // Renamed from performedBy
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