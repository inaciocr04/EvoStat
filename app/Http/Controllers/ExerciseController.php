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
            return MuscleCategory::with('muscleTargets.primaryExercises')->orderBy('name')->get();
        });

        // Cache des exercices avec likes (invalidation lors des modifications)
        $cacheKey = 'exercises_with_likes_' . ($user ? $user->id : 'guest');
        $exercises = Cache::remember($cacheKey, 1800, function () use ($user) {
            return Exercise::with(['muscleTargets' => function($query) {
                $query->withPivot('is_primary');
            }, 'likes'])
                ->withCount('likes')
                ->get()
                ->map(function ($exercise) use ($user) {
                    $exerciseArray = $exercise->toArray();
                    $exerciseArray['is_liked'] = $user ? $exercise->likes->contains('user_id', $user->id) : false;
                    $exerciseArray['likes_count'] = $exercise->likes_count;
                    
                    // S'assurer que les données de pivot sont incluses
                    if (isset($exerciseArray['muscle_targets'])) {
                        foreach ($exerciseArray['muscle_targets'] as $index => $muscle) {
                            if ($exercise->muscleTargets[$index]->pivot) {
                                $exerciseArray['muscle_targets'][$index]['pivot'] = [
                                    'is_primary' => $exercise->muscleTargets[$index]->pivot->is_primary
                                ];
                            }
                        }
                    }
                    
                    // Ajouter les statistiques utilisateur si connecté
                    if ($user) {
                        $exerciseArray['user_stats'] = $this->getUserExerciseStats($exercise->id, $user->id);
                    } else {
                        $exerciseArray['user_stats'] = null;
                    }
                    
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

        // Gérer les muscles ciblés avec distinction principal/secondaire
        $muscleTargetsData = [];
        
        // Ajouter le muscle principal
        if (isset($data['primary_muscle_target'])) {
            $muscleTargetsData[$data['primary_muscle_target']] = ['is_primary' => true];
        }
        
        // Ajouter les muscles secondaires
        if (isset($data['secondary_muscle_targets'])) {
            foreach ($data['secondary_muscle_targets'] as $muscleId) {
                $muscleTargetsData[$muscleId] = ['is_primary' => false];
            }
        }
        
        $exercise->muscleTargets()->sync($muscleTargetsData);

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
        
        // Charger l'exercice avec ses muscles ciblés et les données de pivot
        $exercise = Exercise::with(['muscleTargets' => function($query) {
            $query->withPivot('is_primary');
        }])->find($exercise->id);
        
        // Préparer les données pour Inertia (utiliser muscle_targets pour la cohérence)
        $exerciseData = [
            'id' => $exercise->id,
            'name' => $exercise->name,
            'description' => $exercise->description,
            'muscle_targets' => $exercise->muscleTargets->map(function($muscle) {
                return [
                    'id' => $muscle->id,
                    'name' => $muscle->name,
                    'pivot' => [
                        'is_primary' => $muscle->pivot->is_primary
                    ]
                ];
            })
        ];
        
        return Inertia::render('Exercises/edit', [
            'exercise' => $exerciseData,
            'muscleTargets' => $muscleTargets
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise)
    {
        $data = $request->validated();
        $exercise->fill($data);
        $exercise->save();

        // Gérer les muscles ciblés avec distinction principal/secondaire
        $muscleTargetsData = [];
        
        // Ajouter le muscle principal
        if (isset($data['primary_muscle_target'])) {
            $muscleTargetsData[$data['primary_muscle_target']] = ['is_primary' => true];
        }
        
        // Ajouter les muscles secondaires
        if (isset($data['secondary_muscle_targets'])) {
            foreach ($data['secondary_muscle_targets'] as $muscleId) {
                $muscleTargetsData[$muscleId] = ['is_primary' => false];
            }
        }
        
        $exercise->muscleTargets()->sync($muscleTargetsData);

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

    /**
     * Récupère les statistiques utilisateur pour un exercice
     */
    private function getUserExerciseStats($exerciseId, $userId)
    {
        // Compter le nombre de fois que l'utilisateur a fait cet exercice
        $sessionCount = \DB::table('session_exercises')
            ->join('workout_sessions', 'session_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->where('session_exercises.exercise_id', $exerciseId)
            ->where('workout_sessions.user_id', $userId)
            ->where('workout_sessions.status', 'completed')
            ->count();

        // Dernière fois que l'utilisateur a fait cet exercice
        $lastSession = \DB::table('session_exercises')
            ->join('workout_sessions', 'session_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->where('session_exercises.exercise_id', $exerciseId)
            ->where('workout_sessions.user_id', $userId)
            ->where('workout_sessions.status', 'completed')
            ->orderBy('workout_sessions.completed_at', 'desc')
            ->first();

        // Statistiques des séries (poids max, répétitions max, etc.)
        $setStats = \DB::table('sets')
            ->join('session_exercises', 'sets.session_exercise_id', '=', 'session_exercises.id')
            ->join('workout_sessions', 'session_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->where('session_exercises.exercise_id', $exerciseId)
            ->where('workout_sessions.user_id', $userId)
            ->where('workout_sessions.status', 'completed')
            ->selectRaw('
                MAX(weight) as max_weight,
                MAX(reps) as max_reps,
                AVG(weight) as avg_weight,
                AVG(reps) as avg_reps,
                COUNT(*) as total_sets
            ')
            ->first();

        return [
            'session_count' => $sessionCount,
            'last_session' => $lastSession ? $lastSession->completed_at : null,
            'max_weight' => $setStats->max_weight ?? 0,
            'max_reps' => $setStats->max_reps ?? 0,
            'avg_weight' => round($setStats->avg_weight ?? 0, 1),
            'avg_reps' => round($setStats->avg_reps ?? 0, 1),
            'total_sets' => $setStats->total_sets ?? 0,
        ];
    }
}
