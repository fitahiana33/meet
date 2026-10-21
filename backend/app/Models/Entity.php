<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    protected $fillable = ['title', 'address', 'latitude', 'longitude', 'description'];

    public function grievances(): HasMany
    {
        return $this->hasMany(Grievance::class);
    }
}
