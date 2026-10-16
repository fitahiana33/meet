<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrievanceDocument extends Model
{
    use HasFactory;

    protected $fillable = ['grievance_id', 'original_name', 'path', 'mime_type', 'size'];

    public function grievance(): BelongsTo { return $this->belongsTo(Grievance::class); }
}
