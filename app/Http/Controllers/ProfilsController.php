<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfilsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Optimisation : une seule requête pour toutes les statistiques
        $stats = $this->getAllStats($user);
        $latestWorkouts = $this->lastestWorkout();

        return Inertia::render('Profils', [
            'user' => $user,
            'countWeight' => $stats['weight'],
            'countReps' => $stats['reps'],
            'countWorkouts' => $stats['workouts'],
            'latestWorkouts' => $latestWorkouts
        ]);
    }

    /**
     * Optimisation : calculer toutes les statistiques en une seule requête
     */
    private function getAllStats($user)
    {
        // Requête optimisée pour récupérer toutes les données nécessaires
        $sessions = $user->workoutSessions()
            ->with(['sessionExercises.sets'])
            ->get();

        $allSets = $sessions->flatMap(fn ($session) => $session->sessionExercises)
                           ->flatMap(fn ($exercise) => $exercise->sets);

        $recentSets = $sessions->where('created_at', '>=', now()->subMonths(6))
                              ->flatMap(fn ($session) => $session->sessionExercises)
                              ->flatMap(fn ($exercise) => $exercise->sets);

        return [
            'weight' => $recentSets->sum(fn ($set) => ($set->weight ?? 0) * $set->reps),
            'reps' => $allSets->sum('reps'),
            'workouts' => $sessions->count()
        ];
    }

    private function countWeight()
    {
        $user = auth()->user();

        $sessions = $user->workoutSessions()
            ->where('created_at', '>=', now()->subMonths(6))
            ->with('sessionExercises.sets')
            ->get();

        $totalWeight = $sessions
            ->flatMap(fn ($session) => $session->sessionExercises)
            ->flatMap(fn ($exercise) => $exercise->sets)
            ->sum(fn ($set) => $set->weight * $set->reps);

        return $totalWeight;
    }

    private function countReps()
    {
        $user = auth()->user();

        $sessions = $user->workoutSessions()
            ->with('sessionExercises.sets')
            ->get();

        $totalReps = $sessions
            ->flatMap(fn ($session) => $session->sessionExercises)
            ->flatMap(fn ($exercise) => $exercise->sets)
            ->sum(fn ($set) =>$set->reps);

        return $totalReps;
    }

    private function countWorkouts()
    {
        $user = auth()->user();

        return $user->workoutSessions()->count();
    }

    private function lastestWorkout()
    {
        $user = auth()->user();

        return $user->workoutSessions()
            ->with('workoutTemplate')
            ->with('sessionExercises.sets')
            ->where('status', 'completed')
            ->latest()
            ->take(3)
            ->get();
    }

}
