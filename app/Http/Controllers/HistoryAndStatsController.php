<?php

namespace App\Http\Controllers;

use App\Models\WorkoutSession;
use App\Models\Exercise;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class HistoryAndStatsController extends Controller
{
    public function getStats(Request $request)
    {
        $user = $request->user();
        $exerciseId = $request->query('exercise_id');

        // Récupérer les exercices utilisés par l'utilisateur via Eloquent
        $exercises = Exercise::whereHas('sessionExercises.workoutSession', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

        $stats = collect();
        $exerciseName = null;

        if ($exerciseId) {
            $exercise = DB::table('exercises')->where('id', $exerciseId)->first();
            $exerciseName = $exercise?->name;

            $startDate = Carbon::now()->subMonths(2)->startOfMonth(); // 1er jour d’il y a 2 mois
            $endDate = Carbon::now()->endOfDay();

            $stats = DB::table('sets')
                ->join('session_exercises', 'sets.session_exercise_id', '=', 'session_exercises.id')
                ->join('workout_sessions', 'session_exercises.workout_session_id', '=', 'workout_sessions.id')
                ->select('sets.weight', 'sets.reps', 'workout_sessions.created_at')
                ->where('workout_sessions.user_id', $user->id)
                ->where('session_exercises.exercise_id', $exerciseId)
                ->whereBetween('workout_sessions.created_at', [$startDate, $endDate])
                ->orderBy('workout_sessions.created_at', 'asc')
                ->get();
        }

        return Inertia::render('HistoryAndStats', [
            'exercises' => $exercises,
            'stats' => $stats,
            'exerciseId' => $exerciseId,
            'exerciseName' => $exerciseName,
        ]);
    }
}
