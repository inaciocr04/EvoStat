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
    public function sessionExercise()
    {
        return $this->belongsTo(SessionExercise::class);
    }

    public function exercise()
    {
        return $this->hasOneThrough(Exercise::class, SessionExercise::class, 'id', 'id', 'session_exercise_id', 'exercise_id');
    }

}
