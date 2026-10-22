<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grievance extends Model
{
    use HasFactory;

    public const STATUSES = ['submitted', 'rejected', 'in_progress', 'processed'];

    protected $fillable = [
        'code', 'title', 'sector_id', 'entity_id', 'department_id', 'created_by_id', 'description',
        'status', 'complained_at', 'due_at',
    ];

    protected function casts(): array
    {
        return ['complained_at' => 'datetime', 'due_at' => 'datetime'];
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function declarants(): BelongsToMany
    {
        return $this->belongsToMany(Declarant::class)->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GrievanceDocument::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(GrievanceStatusHistory::class)->latest('changed_at');
    }

    public function assignmentHistories(): HasMany
    {
        return $this->hasMany(GrievanceAssignmentHistory::class)->latest('changed_at');
    }
}
