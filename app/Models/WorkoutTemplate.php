<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutTemplateExercises(): HasMany
    {
        return $this->hasMany(WorkoutTemplateExercise::class);
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'workout_template_exercises')
            ->withPivot('order', 'notes')
            ->withTimestamps();
    }

}
