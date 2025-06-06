<?php

namespace App\Http\Controllers;

use App\Models\MuscleTarget;
use App\Http\Requests\StoreMuscleTargetRequest;
use App\Http\Requests\UpdateMuscleTargetRequest;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class MuscleTargetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $muscleTargets = MuscleTarget::all();

        return Inertia::render('MuscleTargets/index', compact('muscleTargets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('MuscleTargets/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMuscleTargetRequest $request)
    {
        $data = $request->validated();
        $muscleTarget = new MuscleTarget();
        $muscleTarget->fill($data);
        $muscleTarget->save();

        return Redirect::route('muscleTargets.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(MuscleTarget $muscleTarget)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MuscleTarget $muscleTarget)
    {
        return Inertia::render('MuscleTargets/edit', compact('muscleTarget'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMuscleTargetRequest $request, MuscleTarget $muscleTarget)
    {
        $data = $request->validated();
        $muscleTarget->fill($data);
        $muscleTarget->save();

        return Redirect::route('muscleTargets.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MuscleTarget $muscleTarget)
    {
        $muscleTarget->delete();

        return Redirect::route('muscleTargets.index');
    }
}
