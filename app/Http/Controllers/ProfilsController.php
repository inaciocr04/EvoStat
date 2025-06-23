<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfilsController extends Controller
{
    public function index()
    {
        $countWeight = $this->countWeight();
        $countReps = $this->countReps();
        $countWorkouts = $this->countWorkouts();


        return Inertia::render('Profils', compact('countWeight', 'countReps', 'countWorkouts'));
    }

    private function countWeight()
    {
        $user = auth()->user();

        $sessions = $user->workoutSessions()
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

}
