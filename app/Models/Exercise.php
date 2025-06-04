<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{

    protected $fillable = [
        'name',
        'description',
    ];
    public function workoutExercises(): HasMany
    {
        return $this->hasMany(WorkoutTemplateExercise::class);
    }

    public function mucleTargets(): BelongsToMany
    {
        return $this->belongsToMany(MuscleTarget::class, 'exercise_muscle_target');
    }
}
