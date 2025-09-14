<?php

namespace App\Http\Controllers;

use App\Models\WorkoutSession;
use App\Models\Exercise;
use App\Models\Set;
use App\Models\WorkoutTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Statistiques générales
        $generalStats = $this->getGeneralStats($user);
        
        // Statistiques de fréquence
        $frequencyStats = $this->getFrequencyStats($user);
        
        // Statistiques de progression
        $progressStats = $this->getProgressStats($user);
        
        // Records personnels
        $personalRecords = $this->getPersonalRecords($user);
        
        // Statistiques par groupe musculaire
        $muscleGroupStats = $this->getMuscleGroupStats($user);
        
        // Statistiques de durée des séances
        $durationStats = $this->getDurationStats($user);
        
        // Top exercices
        $topExercises = $this->getTopExercises($user);
        
        // Statistiques récentes (30 derniers jours)
        $recentStats = $this->getRecentStats($user);

        return Inertia::render('Stats', [
            'generalStats' => $generalStats,
            'frequencyStats' => $frequencyStats,
            'progressStats' => $progressStats,
            'personalRecords' => $personalRecords,
            'muscleGroupStats' => $muscleGroupStats,
            'durationStats' => $durationStats,
            'topExercises' => $topExercises,
            'recentStats' => $recentStats,
        ]);
    }

    private function getGeneralStats($user)
    {
        $sessions = $user->workoutSessions()->where('status', 'completed')->get();
        
        $totalSets = $sessions->flatMap(fn($session) => $session->sessionExercises)
                            ->flatMap(fn($exercise) => $exercise->sets);
        
        $totalVolume = $totalSets->sum(fn($set) => ($set->weight ?? 0) * $set->reps);
        $totalReps = $totalSets->sum('reps');
        $totalWeight = $totalSets->sum('weight');
        
        return [
            'totalSessions' => $sessions->count(),
            'totalSets' => $totalSets->count(),
            'totalReps' => $totalReps,
            'totalVolume' => $totalVolume,
            'averageWeight' => $totalSets->count() > 0 ? round($totalWeight / $totalSets->count(), 1) : 0,
            'averageReps' => $totalSets->count() > 0 ? round($totalReps / $totalSets->count(), 1) : 0,
        ];
    }

    private function getFrequencyStats($user)
    {
        $sessions = $user->workoutSessions()->where('status', 'completed')->get();
        
        // Fréquence hebdomadaire (dernières 12 semaines)
        $weeklyFrequency = [];
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            
            $weeklyFrequency[] = [
                'week' => $weekStart->format('W'),
                'date' => $weekStart->format('M d'),
                'sessions' => $sessions->whereBetween('created_at', [$weekStart, $weekEnd])->count()
            ];
        }
        
        // Fréquence mensuelle (derniers 12 mois)
        $monthlyFrequency = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $monthlyFrequency[] = [
                'month' => $monthStart->format('M Y'),
                'sessions' => $sessions->whereBetween('created_at', [$monthStart, $monthEnd])->count()
            ];
        }
        
        return [
            'weekly' => $weeklyFrequency,
            'monthly' => $monthlyFrequency,
            'averagePerWeek' => $sessions->count() > 0 ? round($sessions->count() / max(1, $sessions->min('created_at')->diffInWeeks(Carbon::now())), 1) : 0,
            'averagePerMonth' => $sessions->count() > 0 ? round($sessions->count() / max(1, $sessions->min('created_at')->diffInMonths(Carbon::now())), 1) : 0,
        ];
    }

    private function getProgressStats($user)
    {
        // Progression des 10 exercices les plus utilisés
        $topExercises = DB::table('sets')
            ->join('session_exercises', 'sets.session_exercise_id', '=', 'session_exercises.id')
            ->join('workout_sessions', 'session_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->join('exercises', 'session_exercises.exercise_id', '=', 'exercises.id')
            ->where('workout_sessions.user_id', $user->id)
            ->where('workout_sessions.status', 'completed')
            ->select('exercises.id', 'exercises.name', DB::raw('AVG(sets.weight) as avg_weight'), DB::raw('COUNT(*) as total_sets'))
            ->groupBy('exercises.id', 'exercises.name')
            ->orderBy('total_sets', 'desc')
            ->limit(10)
            ->get();

        $progressData = [];
        foreach ($topExercises as $exercise) {
            // Récupérer les données des 3 derniers mois pour cet exercice
            $recentData = DB::table('sets')
                ->join('session_exercises', 'sets.session_exercise_id', '=', 'session_exercises.id')
                ->join('workout_sessions', 'session_exercises.workout_session_id', '=', 'workout_sessions.id')
                ->where('workout_sessions.user_id', $user->id)
                ->where('workout_sessions.status', 'completed')
                ->where('session_exercises.exercise_id', $exercise->id)
                ->where('workout_sessions.created_at', '>=', Carbon::now()->subMonths(3))
                ->select('sets.weight', 'sets.reps', 'workout_sessions.created_at')
                ->orderBy('workout_sessions.created_at')
                ->get();

            $progressData[] = [
                'exercise' => $exercise->name,
                'exercise_id' => $exercise->id,
                'total_sets' => $exercise->total_sets,
                'avg_weight' => round($exercise->avg_weight, 1),
                'recent_data' => $recentData->map(fn($set) => [
                    'weight' => $set->weight,
                    'reps' => $set->reps,
                    'date' => Carbon::parse($set->created_at)->format('Y-m-d'),
                    'timestamp' => Carbon::parse($set->created_at)->timestamp * 1000
                ])
            ];
        }

        return $progressData;
    }

    private function getPersonalRecords($user)
    {
        try {
            $records = DB::table('sets')
                ->join('session_exercises', 'sets.session_exercise_id', '=', 'session_exercises.id')
                ->join('workout_sessions', 'session_exercises.workout_session_id', '=', 'workout_sessions.id')
                ->join('exercises', 'session_exercises.exercise_id', '=', 'exercises.id')
                ->where('workout_sessions.user_id', $user->id)
                ->where('workout_sessions.status', 'completed')
                ->select(
                    'exercises.name',
                    DB::raw('MAX(sets.weight) as max_weight'),
                    DB::raw('MAX(sets.reps) as max_reps'),
                    DB::raw('MAX(sets.weight * sets.reps) as max_volume'),
                    DB::raw('MAX(workout_sessions.created_at) as last_achieved')
                )
                ->groupBy('exercises.id', 'exercises.name')
                ->having('max_weight', '>', 0)
                ->orderBy('max_weight', 'desc')
                ->limit(10)
                ->get();

            return $records->map(fn($record) => [
                'exercise' => $record->name,
                'max_weight' => $record->max_weight ?? 0,
                'max_reps' => $record->max_reps ?? 0,
                'max_volume' => $record->max_volume ?? 0,
                'last_achieved' => Carbon::parse($record->last_achieved)->format('d M Y')
            ]);
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function getMuscleGroupStats($user)
    {
        try {
            $muscleStats = DB::table('sets')
                ->join('session_exercises', 'sets.session_exercise_id', '=', 'session_exercises.id')
                ->join('workout_sessions', 'session_exercises.workout_session_id', '=', 'workout_sessions.id')
                ->join('exercises', 'session_exercises.exercise_id', '=', 'exercises.id')
                ->join('exercise_muscle_target', 'exercises.id', '=', 'exercise_muscle_target.exercise_id')
                ->join('muscle_targets', 'exercise_muscle_target.muscle_target_id', '=', 'muscle_targets.id')
                ->join('muscle_categories', 'muscle_targets.muscle_category_id', '=', 'muscle_categories.id')
                ->where('workout_sessions.user_id', $user->id)
                ->where('workout_sessions.status', 'completed')
                ->select(
                    'muscle_categories.name as category_name',
                    DB::raw('COUNT(DISTINCT workout_sessions.id) as sessions_count'),
                    DB::raw('SUM(sets.weight * sets.reps) as total_volume'),
                    DB::raw('AVG(sets.weight) as avg_weight'),
                    DB::raw('SUM(sets.reps) as total_reps')
                )
                ->groupBy('muscle_categories.id', 'muscle_categories.name')
                ->orderBy('total_volume', 'desc')
                ->get();

            return $muscleStats->map(fn($stat) => [
                'category' => $stat->category_name,
                'sessions' => $stat->sessions_count,
                'total_volume' => $stat->total_volume ?? 0,
                'avg_weight' => round($stat->avg_weight ?? 0, 1),
                'total_reps' => $stat->total_reps ?? 0
            ]);
        } catch (\Exception $e) {
            // Si la requête échoue (pas de données de muscle), retourner un tableau vide
            return collect();
        }
    }

    private function getDurationStats($user)
    {
        $sessions = $user->workoutSessions()
            ->where('status', 'completed')
            ->whereNotNull('total_duration')
            ->get();

        if ($sessions->isEmpty()) {
            return [
                'average_duration' => 0,
                'total_time' => 0,
                'longest_session' => 0,
                'shortest_session' => 0,
                'sessions_with_duration' => 0
            ];
        }

        $durations = $sessions->pluck('total_duration');
        
        return [
            'average_duration' => round($durations->avg() / 60, 1), // en minutes
            'total_time' => round($durations->sum() / 3600, 1), // en heures
            'longest_session' => round($durations->max() / 60, 1), // en minutes
            'shortest_session' => round($durations->min() / 60, 1), // en minutes
            'sessions_with_duration' => $sessions->count()
        ];
    }

    private function getTopExercises($user)
    {
        return DB::table('sets')
            ->join('session_exercises', 'sets.session_exercise_id', '=', 'session_exercises.id')
            ->join('workout_sessions', 'session_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->join('exercises', 'session_exercises.exercise_id', '=', 'exercises.id')
            ->where('workout_sessions.user_id', $user->id)
            ->where('workout_sessions.status', 'completed')
            ->select(
                'exercises.name',
                DB::raw('COUNT(DISTINCT workout_sessions.id) as sessions_count'),
                DB::raw('COUNT(sets.id) as sets_count'),
                DB::raw('SUM(sets.weight * sets.reps) as total_volume'),
                DB::raw('AVG(sets.weight) as avg_weight')
            )
            ->groupBy('exercises.id', 'exercises.name')
            ->orderBy('sessions_count', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($exercise) => [
                'name' => $exercise->name,
                'sessions' => $exercise->sessions_count,
                'sets' => $exercise->sets_count,
                'total_volume' => $exercise->total_volume,
                'avg_weight' => round($exercise->avg_weight, 1)
            ]);
    }

    private function getRecentStats($user)
    {
        $recentSessions = $user->workoutSessions()
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->get();

        $recentSets = $recentSessions->flatMap(fn($session) => $session->sessionExercises)
                                   ->flatMap(fn($exercise) => $exercise->sets);

        return [
            'sessions_last_30_days' => $recentSessions->count(),
            'sets_last_30_days' => $recentSets->count(),
            'volume_last_30_days' => $recentSets->sum(fn($set) => ($set->weight ?? 0) * $set->reps),
            'average_sessions_per_week' => $recentSessions->count() > 0 ? round($recentSessions->count() / 4.3, 1) : 0
        ];
    }
}
