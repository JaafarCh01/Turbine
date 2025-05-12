<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';
    public $timestamps = false; // Assuming no timestamps based on UML

    protected $fillable = [
        'revisionId',
        'description',
        'plannedAt',
        'doneAt',
    ];

    protected $casts = [
        'plannedAt' => 'datetime',
        'doneAt' => 'datetime',
    ];

    // Relationships from UML
    public function revision(): BelongsTo
    {
        return $this->belongsTo(Revision::class, 'revisionId');
    }
} 