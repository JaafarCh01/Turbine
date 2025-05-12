<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningAssignment extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'planningId',
        'userId',
        'roleDansPlanning', // Consider renaming for clarity/convention e.g., assignment_role
    ];

    protected $casts = [
        'roleDansPlanning' => Role::class,
        // No timestamps needed based on UML, unless required by convention/framework
    ];

    // Relationships from UML
    public function planning(): BelongsTo
    {
        return $this->belongsTo(Planning::class, 'planningId');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }
} 