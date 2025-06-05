<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MuscleTarget extends Model
{

    protected $fillable = [
        'name',
    ];
    public function exercises()
    {
        return $this->belongsToMany(Exercise::class);
    }
}
