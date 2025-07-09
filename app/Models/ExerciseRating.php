<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseRating extends Model
{
    protected $fillable = ['user_id', 'exercise_id', 'rating'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
