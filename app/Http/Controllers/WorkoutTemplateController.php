<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\WorkoutTemplate;
use App\Http\Requests\StoreWorkoutTemplateRequest;
use App\Http\Requests\UpdateWorkoutRequest;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class WorkoutTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workoutTemplates = WorkoutTemplate::with('workoutTemplateExercises.exercise')->where('user_id', auth()->id())->get();
        $exercises = Exercise::all();

        return Inertia::render('WorkoutTemplates/index', compact('workoutTemplates', 'exercises'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Inertia\Response
    {
        $exercises = Exercise::all();

        return Inertia::render('WorkoutTemplates/create', compact('exercises'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkoutTemplateRequest $request)
    {
        $data = $request->validated();
        $workoutTemplate = WorkoutTemplate::create([
            'name' => $data['name'],
            'user_id' => auth()->id(),
        ]);

        foreach ($data['exercises'] as $exercise) {
            $workoutTemplate->workoutTemplateExercises()->create([
                'exercise_id' => $exercise['exercise_id'],
                'order' => $exercise['order'],
                'notes' => $exercise['notes'] ?? null,
            ]);
        }

        return Redirect::route('workout-templates.index')->with('success', 'Template créé.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkoutTemplate $workout)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkoutTemplate $workout)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkoutRequest $request, WorkoutTemplate $workout)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkoutTemplate $workout)
    {
        $workout->delete();

        return Redirect::route('workout-templates.index')->with('success', 'Template supprimer.');
    }
}
