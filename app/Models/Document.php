<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Document extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'title',
        'fileData', // Note: Storing file data directly in DB is often discouraged. Consider storing path/reference.
        'type',
        'category',
        'uploadDate',
        'uploadedBy',
        'turbineId' // Added based on Turbine relationship assumption
    ];

    protected $casts = [
        'type' => DocumentType::class,
        'category' => DocumentCategory::class,
        'uploadDate' => 'datetime',
        'createdAt' => 'datetime', // Assuming standard timestamps
        'updatedAt' => 'datetime', // Assuming standard timestamps
    ];

    // Relationships from UML
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploadedBy');
    }

    public function turbine(): BelongsTo
    {
        return $this->belongsTo(Turbine::class, 'turbineId');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable'); // Assuming polymorphic for comments
    }
} 