<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Declarant extends Model
{
    protected $fillable = ['first_name', 'last_name', 'email', 'address', 'phone', 'photo_path'];

    public function grievances(): BelongsToMany
    {
        return $this->belongsToMany(Grievance::class)->withTimestamps();
    }

    public function displayName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
