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

    public function workoutTemplates()
    {
        return $this->belongsToMany(WorkoutTemplate::class, 'workout_template_exercises')
            ->withPivot('order', 'notes')
            ->withTimestamps();
    }

    public function muscleTargets(): BelongsToMany
    {
        return $this->belongsToMany(MuscleTarget::class);
    }

    public function sets()
    {
        return $this->hasMany(Set::class);
    }
}
