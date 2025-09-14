<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutSession extends Model
{
    protected $fillable = [
        'user_id',
        'workout_template_id',
        'status',
        'started_at',
        'completed_at',
        'total_duration',
    ];

    public function workoutTemplate() {
        return $this->belongsTo(WorkoutTemplate::class);
    }

//    public function exercises()
//    {
//        return $this->belongsToMany(Exercise::class, 'workout_template_exercises', 'workout_session_id', 'exercise_id');
//    }


    public function sessionExercises()
    {
        return $this->hasMany(SessionExercise::class, 'workout_session_id');
    }
}
