<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutTemplateExercise extends Model
{
    protected $fillable = [
        'workout_template_id',
        'exercise_id',
        'order',
        'notes',
        'estimated_sets',
        'estimated_reps',
        'estimated_weight',
        'estimated_rest_time',
    ];

    public function workoutTemplate()
    {
        return $this->belongsTo(WorkoutTemplate::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

}
