<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdrStep extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';
    public $timestamps = false; // Assuming no timestamps based on UML

    protected $fillable = [
        'pdrId',
        'description',
        'mandatory',
        'ordre', // French for order/sequence
    ];

    protected $casts = [
        'mandatory' => 'boolean',
        'ordre' => 'integer',
    ];

    // Relationships from UML
    public function pdr(): BelongsTo
    {
        return $this->belongsTo(PDR::class, 'pdrId');
    }
} 