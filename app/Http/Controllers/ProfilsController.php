<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfilsController extends Controller
{
    public function index()
    {
        $countWeight = $this->countWeight();
        $countReps = $this->countReps();
        $countWorkouts = $this->countWorkouts();
        $latestWorkouts = $this->lastestWorkout();

//        dd($latestWorkouts);

        $user = auth()->user();


        return Inertia::render('Profils', compact('user','countWeight', 'countReps', 'countWorkouts', 'latestWorkouts'));
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
