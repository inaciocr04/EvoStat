<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\MuscleTarget;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exercises = Exercise::with('muscleTargets')->get();

        return Inertia::render('Exercises/index', compact('exercises'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $muscleTargets = MuscleTarget::all();
        return Inertia::render('Exercises/create', compact('muscleTargets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExerciseRequest $request)
    {
        $data = $request->validated();
        $exercise = new Exercise();
        $exercise->fill($data);
        $exercise->save();

        $exercise->muscleTargets()->sync($data['muscle_targets'] ?? []);

        return Redirect::route('exercises.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Exercise $exercise)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exercise $exercise)
    {
        $muscleTargets = MuscleTarget::all();
        return Inertia::render('Exercises/edit', compact('exercise','muscleTargets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise)
    {
        $data = $request->validated();
        $exercise->fill($data);
        $exercise->save();

        $exercise->muscleTargets()->sync($data['muscle_targets'] ?? []);


        return Redirect::route('exercises.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exercise $exercise)
    {
        $exercise->muscleTargets()->detach();

        $exercise->delete();

        return Redirect::route('exercises.index');
    }
}
