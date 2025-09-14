<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\MuscleCategory;
use App\Models\MuscleTarget;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Cache des catégories musculaires (rarement modifiées)
        $muscleCategories = Cache::remember('muscle_categories', 3600, function () {
            return MuscleCategory::with('muscleTargets.exercises')->orderBy('name')->get();
        });

        // Cache des exercices avec likes (invalidation lors des modifications)
        $cacheKey = 'exercises_with_likes_' . ($user ? $user->id : 'guest');
        $exercises = Cache::remember($cacheKey, 1800, function () use ($user) {
            return Exercise::with(['muscleTargets', 'likes'])
                ->withCount('likes')
                ->get()
                ->map(function ($exercise) use ($user) {
                    $exerciseArray = $exercise->toArray();
                    $exerciseArray['is_liked'] = $user ? $exercise->likes->contains('user_id', $user->id) : false;
                    $exerciseArray['likes_count'] = $exercise->likes_count;
                    return $exerciseArray;
                });
        });

        return Inertia::render('Exercises/index', [
            'muscleCategories' => $muscleCategories,
            'exercises' => $exercises,
        ]);
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

        // Invalider le cache des exercices
        Cache::forget('muscle_categories');
        Cache::flush(); // Invalider tous les caches pour être sûr

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

        // Invalider le cache des exercices
        Cache::forget('muscle_categories');
        Cache::flush(); // Invalider tous les caches pour être sûr

        return Redirect::route('exercises.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exercise $exercise)
    {
        $exercise->muscleTargets()->detach();

        $exercise->delete();

        // Invalider le cache des exercices
        Cache::forget('muscle_categories');
        Cache::flush(); // Invalider tous les caches pour être sûr

        return Redirect::route('exercises.index');
    }
}
