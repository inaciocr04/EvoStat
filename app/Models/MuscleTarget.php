<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MuscleTarget extends Model
{

    protected $fillable = [
        'name',
        'muscle_category_id'
    ];
    public function exercises()
    {
        return $this->belongsToMany(Exercise::class);
    }

    public function muscle_categories()
    {
        return $this->belongsTo(MuscleCategory::class);
    }
}
