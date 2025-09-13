<?php

namespace App\Http\Controllers;

use App\Models\MuscleCategory;
use App\Http\Requests\StoreMuscleCategoryRequest;
use App\Http\Requests\UpdateMuscleCategoryRequest;
use Inertia\Inertia;

class MuscleCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $muscleCategories = MuscleCategory::orderBy('name', 'asc')->get();
        return Inertia::render("MuscleCategories/index",compact("muscleCategories"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('MuscleCategories/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMuscleCategoryRequest $request)
    {
        $data = $request->validated();
        $muscleCategory = new MuscleCategory();
        $muscleCategory->fill($data);
        $muscleCategory->save();

        return redirect()->route('muscleCategories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(MuscleCategory $muscleCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MuscleCategory $muscleCategory)
    {
        return Inertia::render('MuscleCategories/edit', compact('muscleCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMuscleCategoryRequest $request, MuscleCategory $muscleCategory)
    {
        $data = $request->validated();
        $muscleCategory->fill($data);
        $muscleCategory->save();
        return redirect()->route('muscleCategories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MuscleCategory $muscleCategory)
    {
        $muscleCategory->delete();
        return redirect()->route('muscleCategories.index');
    }
}
