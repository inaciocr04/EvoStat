<?php

namespace App\Http\Controllers;

use App\Models\WorkoutSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryAndStatsController extends Controller
{
    public function Workouts()
    {
        $workoutCompleted = WorkoutSession::where('status', 'completed')->get();
        $workoutDraft = WorkoutSession::where('status', 'draft')->get();
        $workoutInProgress = WorkoutSession::where('status', 'in_progress')->get();

        return Inertia::render('WorkoutSessions/HistoryAndStats', compact('workoutCompleted', 'workoutDraft', 'workoutInProgress'));
    }
}
