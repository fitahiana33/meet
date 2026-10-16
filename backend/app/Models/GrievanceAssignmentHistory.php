<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrievanceAssignmentHistory extends Model
{
    use HasFactory;

    protected $fillable = ['grievance_id', 'department_id', 'changed_by_id', 'changed_at'];
    protected function casts(): array { return ['changed_at' => 'datetime']; }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'changed_by_id'); }
}
