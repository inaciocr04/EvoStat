<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutSession extends Model
{
    public function workoutTemplate() {
        return $this->belongsTo(WorkoutTemplate::class);
    }

    public function exercises() {
        return $this->belongsToMany(Exercise::class, 'session_exercise')
            ->withPivot('weight', 'sets_done', 'reps_done', 'notes');
    }
}
