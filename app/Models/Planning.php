<?php

namespace App\Models;

use App\Enums\PlanningStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planning extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'turbineId',
        'startDate',
        'endDate',
        'createdBy',
        'status',
    ];

    protected $casts = [
        'startDate' => 'date',
        'endDate' => 'date',
        'status' => PlanningStatus::class,
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    // Relationships from UML
    public function turbine(): BelongsTo
    {
        return $this->belongsTo(Turbine::class, 'turbineId');
    }

    public function creator(): BelongsTo // Renamed from createdBy for convention
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PlanningAssignment::class, 'planningId');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($planning) {
            // Delete related planning assignments
            $planning->assignments()->each(function ($assignment) {
                $assignment->delete();
            });
        });
    }
} 