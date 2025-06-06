<?php

namespace App\Http\Controllers;

use App\Models\WorkoutSession;
use App\Http\Requests\StoreWorkoutSessionRequest;
use App\Http\Requests\UpdateWorkoutSessionRequest;
use App\Models\WorkoutTemplate;

class WorkoutSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkoutSessionRequest $request)
    {
        //
    }

//    public function startFromTemplate(WorkoutTemplate $template)
//    {
//        $session = WorkoutSession::create([
//            'user_id' => auth()->id(),
//            'workout_template_id' => $template->id,
//            'started_at' => now(),
//        ]);
//
//        foreach ($template->workoutTemplateExercises as $templateExercise) {
//            $session->sessionExercises()->create([
//                'exercise_id' => $templateExercise->exercise_id,
//                'order' => $templateExercise->order,
//                'notes' => $templateExercise->notes,
//            ]);
//        }
//
//        return redirect()->route('sessions.show', $session);
//    }
    /**
     * Display the specified resource.
     */
    public function show(WorkoutSession $workoutSession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkoutSession $workoutSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkoutSessionRequest $request, WorkoutSession $workoutSession)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkoutSession $workoutSession)
    {
        //
    }
}
