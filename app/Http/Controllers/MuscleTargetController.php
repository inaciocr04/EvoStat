<?php

namespace App\Http\Controllers;

use App\Models\MuscleCategory;
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
        $muscleCategories = MuscleCategory::with('muscleTargets')->get();

        $uncategorizedMuscleTargets = MuscleTarget::where('muscle_category_id', 0)->get();

        return Inertia::render('MuscleTargets/index', compact('uncategorizedMuscleTargets', 'muscleCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $muscleCategories = MuscleCategory::all();
        return Inertia::render('MuscleTargets/create', compact('muscleCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMuscleTargetRequest $request)
    {
        $data = $request->validated();
        $muscleTarget = new MuscleTarget();
        $muscleTarget->fill($data);
        $muscleTarget->muscle_category_id = $data['muscle_category_id'];
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
        $muscleCategories = MuscleCategory::all();
        return Inertia::render('MuscleTargets/edit', compact('muscleTarget', 'muscleCategories'));
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
