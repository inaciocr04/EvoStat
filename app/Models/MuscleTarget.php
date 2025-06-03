<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MuscleTarget extends Model
{
    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'exercise_muscle_target');
    }
}
