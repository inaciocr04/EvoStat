<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\SessionExercise;
use App\Models\WorkoutSession;
use App\Models\WorkoutTemplate;
use App\Http\Requests\StoreWorkoutTemplateRequest;
use App\Http\Requests\UpdateWorkoutTemplateRequest;
use App\Services\EstimatedSetsService;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class WorkoutTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupérer les templates de séance avec les exercices triés par ordre
        $workoutTemplates = WorkoutTemplate::with(['workoutTemplateExercises' => function($query) {
            $query->orderBy('order'); // Trier les exercices par ordre
        }, 'workoutTemplateExercises.exercise'])
            ->where('user_id', auth()->id())
            ->get();

        $exercises = Exercise::all();
        $workoutCompleted = WorkoutSession::with('workoutTemplate', 'sessionExercises.exercise', 'sessionExercises.sets')->where('user_id', auth()->id())->where('status', 'completed')->get();
        $workoutDraft = WorkoutSession::with('workoutTemplate', 'sessionExercises.exercise', 'sessionExercises.sets')->where('user_id', auth()->id())->where('status', 'draft')->get();
        $workoutInProgress = WorkoutSession::with('workoutTemplate', 'sessionExercises.exercise', 'sessionExercises.sets')->where('user_id', auth()->id())->where('status', 'in_progress')->get();


        return Inertia::render('WorkoutTemplates/index', compact('workoutTemplates', 'exercises', 'workoutCompleted', 'workoutDraft', 'workoutInProgress'));
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
        $estimatedSetsService = new EstimatedSetsService();
        
        $workoutTemplate = WorkoutTemplate::create([
            'name' => $data['name'],
            'user_id' => auth()->id(),
        ]);

        foreach ($data['exercises'] as $exercise) {
            // Récupérer l'exercice pour générer des estimations intelligentes si nécessaire
            $exerciseModel = Exercise::find($exercise['exercise_id']);
            
            $workoutTemplate->workoutTemplateExercises()->create([
                'exercise_id' => $exercise['exercise_id'],
                'order' => $exercise['order'],
                'notes' => $exercise['notes'] ?? null,
                // Utiliser les valeurs saisies par l'utilisateur
                'estimated_sets' => $exercise['estimated_sets'],
                'estimated_reps' => $exercise['estimated_reps'],
                'estimated_weight' => $exercise['estimated_weight'] ?? $estimatedSetsService->generateSmartEstimatedWeight(auth()->id(), $exercise['exercise_id']),
                'estimated_rest_time' => $exercise['estimated_rest_time'] * 60, // Convertir minutes en secondes
            ]);
        }

        return Redirect::route('workout-templates.index')
            ->with('success', 'Template créé avec séries estimées.')
            ->with('template_id', $workoutTemplate->id);
    }

    /**
     * Store a newly created resource and start session immediately
     */
    public function storeAndStart(StoreWorkoutTemplateRequest $request)
    {
        $data = $request->validated();
        $estimatedSetsService = new EstimatedSetsService();
        
        $workoutTemplate = WorkoutTemplate::create([
            'name' => $data['name'],
            'user_id' => auth()->id(),
        ]);

        foreach ($data['exercises'] as $exercise) {
            // Récupérer l'exercice pour générer des estimations intelligentes si nécessaire
            $exerciseModel = Exercise::find($exercise['exercise_id']);
            
            $workoutTemplate->workoutTemplateExercises()->create([
                'exercise_id' => $exercise['exercise_id'],
                'order' => $exercise['order'],
                'notes' => $exercise['notes'] ?? null,
                // Utiliser les valeurs saisies par l'utilisateur
                'estimated_sets' => $exercise['estimated_sets'],
                'estimated_reps' => $exercise['estimated_reps'],
                'estimated_weight' => $exercise['estimated_weight'] ?? $estimatedSetsService->generateSmartEstimatedWeight(auth()->id(), $exercise['exercise_id']),
                'estimated_rest_time' => $exercise['estimated_rest_time'] * 60, // Convertir minutes en secondes
            ]);
        }

        // Créer la session directement
        $session = WorkoutSession::create([
            'user_id' => auth()->id(),
            'workout_template_id' => $workoutTemplate->id,
            'status' => 'draft',
        ]);

        // Créer les exercices de session et les séries estimées
        foreach ($workoutTemplate->workoutTemplateExercises as $templateExercise) {
            $sessionExercise = SessionExercise::create([
                'workout_session_id' => $session->id,
                'exercise_id' => $templateExercise->exercise_id,
                'order' => $templateExercise->order,
                'notes' => $templateExercise->notes,
            ]);

            // Générer automatiquement les séries estimées
            $estimatedSetsService->createEstimatedSets($sessionExercise, $templateExercise);
        }

        // Rediriger vers la session créée
        return redirect("/sessions/{$session->id}");
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkoutTemplate $workoutTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkoutTemplate $workoutTemplate)
    {
        $exercises = Exercise::all();

        $workoutTemplate->load('workoutTemplateExercises.exercise');

        $workoutTemplate->exercises = $workoutTemplate->workoutTemplateExercises->map(function($wte) {
            return [
                'id' => $wte->id,
                'exercise_id' => $wte->exercise_id,
                'order' => $wte->order,
                'notes' => $wte->notes,
                'estimated_sets' => $wte->estimated_sets,
                'estimated_reps' => $wte->estimated_reps,
                'estimated_weight' => $wte->estimated_weight,
                'estimated_rest_time' => $wte->estimated_rest_time / 60, // Convertir secondes en minutes
                'name' => $wte->exercise->name ?? 'Exercice inconnu',
            ];
        });


        return Inertia::render('WorkoutTemplates/edit', compact('workoutTemplate', 'exercises'));    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkoutTemplateRequest $request, WorkoutTemplate $workoutTemplate)
    {
        $data = $request->validated();

        $workoutTemplate->update([
            'name' => $data['name'],
            'user_id' => auth()->id(),
        ]);

        foreach ($data['exercises'] as $exercise) {
            $workoutTemplate->workoutTemplateExercises()->updateOrCreate(
                ['exercise_id' => $exercise['exercise_id']],
                [
                    'order' => $exercise['order'],
                    'notes' => $exercise['notes'] ?? null,
                    'estimated_sets' => $exercise['estimated_sets'],
                    'estimated_reps' => $exercise['estimated_reps'],
                    'estimated_weight' => $exercise['estimated_weight'],
                    'estimated_rest_time' => $exercise['estimated_rest_time'] * 60, // Convertir minutes en secondes
                ]
            );
        }

        return Redirect::route('workout-templates.index')->with('success', 'Template modifié.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkoutTemplate $workout_template)
    {
        $workout_template->workoutTemplateExercises()->delete();

        $workout_template->delete();

        return Redirect::route('workout-templates.index')->with('success', 'Template supprimer.');
    }

//    public function Workouts()
//    {
//
//        return Inertia::render('WorkoutTemplates/index', compact('workoutCompleted', 'workoutDraft', 'workoutInProgress'));
//    }

}
