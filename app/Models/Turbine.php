<?php

namespace App\Models;

use App\Enums\TurbineStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Turbine extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'location', 'status'];
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $casts = [
        'status' => TurbineStatus::class,
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    // Relationships from UML
    public function documents(): HasMany
    {
        // Assuming a pivot table or direct FK if Document only belongs to one Turbine
        // Let's assume a direct foreign key 'turbineId' on Document for now
        return $this->hasMany(Document::class, 'turbineId');
    }

    public function plannings(): HasMany
    {
        return $this->hasMany(Planning::class, 'turbineId');
    }

    public function pdrs(): HasMany
    {
        return $this->hasMany(PDR::class, 'turbineId');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class, 'turbineId');
    }

    protected static function boot(){
        parent::boot();
        static::creating(function ($model){
            $model->id = (string) Str::uuid();
        });
    }
}
