<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    protected $fillable = [
        'id',
        'session_exercise_id',
        'weight',
        'reps',
        'rest_time',
    ];
    public function workoutExercise()
    {
        return $this->belongsTo(WorkoutTemplateExercise::class);
    }

    public function exercises()
    {
        return $this->belongsTo(Exercise::class);
    }

}
