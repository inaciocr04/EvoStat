<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MuscleCategory extends Model
{
    protected $fillable = ['name'];

    public function muscleTargets()
    {
        return $this->hasMany(MuscleTarget::class);
    }
}
