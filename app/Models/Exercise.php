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

    public function sessionExercises()
    {
        return $this->hasMany(SessionExercise::class);
    }

    public function likes()
    {
        return $this->hasMany(ExerciseLike::class);
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function ratings()
    {
        return $this->hasMany(ExerciseRating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

//    public function toggleLikeBy(User $user)
//    {
//        if ($this->isLikedBy($user)) {
//            $this->likes()->where('user_id', $user->id)->delete();
//        } else {
//            $this->likes()->create([
//                'user_id' => $user->id,
//            ]);
//        }
//        return response()->noContent();
//
//    }

}
