<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionExercise extends Model
{
    protected $fillable = [
        'workout_session_id',
        'exercise_id',
        'order',
        'notes',
    ];


    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function workoutSession()
    {
        return $this->belongsTo(WorkoutSession::class);
    }
    public function workoutTemplate() {
        return $this->belongsTo(WorkoutTemplate::class);
    }

    public function sets()
    {
        return $this->hasMany(Set::class, 'session_exercise_id');
    }
}
