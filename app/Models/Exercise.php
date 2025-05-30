<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    public function workoutExercises()
    {
        return $this->hasMany(WorkoutExercise::class);
    }

    public function workouts()
    {
        return $this->belongsToMany(Workout::class, 'workout_exercises');
    }
}
