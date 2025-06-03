<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutTemplateExercise extends Model
{
    protected $fillable = [
        'workout_id',
        'exercise_id',
        'order',
        'notes',
    ];

    public function workout()
    {
        return $this->belongsTo(WorkoutTemplate::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function sets()
    {
        return $this->hasMany(Set::class);
    }
}
