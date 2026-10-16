<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrievanceStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = ['grievance_id', 'status', 'changed_by_id', 'changed_at'];
    protected function casts(): array { return ['changed_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'changed_by_id'); }
}
