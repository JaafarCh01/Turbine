<?php

namespace App\Models;

use App\Enums\PDRStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PDR extends Model // Consider renaming if PDR causes issues
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $table = 'pdrs'; // Explicitly defining table name

    protected $fillable = [
        'turbineId',
        'title',
        'status',
        'createdBy',
        'approverId',
        'approvedAt',
    ];

    protected $casts = [
        'status' => PDRStatus::class,
        'approvedAt' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    // Relationships from UML
    public function turbine(): BelongsTo
    {
        return $this->belongsTo(Turbine::class, 'turbineId');
    }

    public function creator(): BelongsTo // Renamed from createdBy
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approverId');
    }

    public function steps(): HasMany // Renamed from pdrSteps
    {
        return $this->hasMany(PdrStep::class, 'pdrId');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable'); // Assuming polymorphic
    }

    public function revision(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Revision::class, 'pdr_id');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pdr_user', 'pdr_id', 'user_id');
    }
} 
