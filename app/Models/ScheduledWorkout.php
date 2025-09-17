<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ScheduledWorkout extends Model
{
    protected $fillable = [
        'user_id',
        'workout_template_id',
        'scheduled_date',
        'scheduled_time',
        'status',
        'workout_session_id',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkoutTemplate::class);
    }

    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class);
    }

    public function getScheduledDateTimeAttribute()
    {
        if ($this->scheduled_time) {
            return Carbon::parse($this->scheduled_date . ' ' . $this->scheduled_time);
        }
        return Carbon::parse($this->scheduled_date);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'scheduled' => 'blue',
            'completed' => 'green',
            'skipped' => 'yellow',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'scheduled' => '📅',
            'completed' => '✅',
            'skipped' => '⏭️',
            'cancelled' => '❌',
            default => '❓'
        };
    }
}
