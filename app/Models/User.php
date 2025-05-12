<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasApiTokens;

    public $incrementing = false;
    protected $keyType = 'uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'passwordHash',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'passwordHash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'passwordHash' => 'hashed',
            'role' => Role::class,
            'createdAt' => 'datetime',
            'updatedAt' => 'datetime',
        ];
    }

    // Relationships from UML
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'uploadedBy');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'userId');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipientId');
    }

    public function planningAssignments(): HasMany
    {
        return $this->hasMany(PlanningAssignment::class, 'userId');
    }

    public function approvedPdrs(): HasMany
    {
        return $this->hasMany(PDR::class, 'approverId');
    }

    public function createdPlannings(): HasMany
    {
        return $this->hasMany(Planning::class, 'createdBy');
    }

    public function performedRevisions(): HasMany
    {
        return $this->hasMany(Revision::class, 'performedBy');
    }

    /**
     * Get the name of the column that should be used for the password.
     *
     * @return string
     */
    public function getAuthPasswordName()
    {
        return 'passwordHash';
    }
}
