<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSetRequest;
use App\Http\Requests\StoreWorkoutSessionRequest;
use App\Http\Requests\StoreSessionExerciceRequest;
use App\Http\Requests\UpdateWorkoutSessionRequest;
use App\Models\SessionExercise;
use App\Models\WorkoutSession;
use App\Models\WorkoutTemplate;
use App\Models\Set;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WorkoutSessionController extends Controller
{
    public function createSessionFromTemplate(StoreWorkoutSessionRequest $request)
    {
        try {
            $validated = $request->validated(); // ← ici DANS le try
            Log::info('createSessionFromTemplate called', $validated);

            $user = $request->user();
            $session = null;

            DB::transaction(function () use ($validated, $user, &$session) {
                $template = WorkoutTemplate::with('exercises')->findOrFail($validated['template_id']);

                $session = WorkoutSession::create([
                    'user_id' => $user->id,
                    'workout_template_id' => $template->id,
                    'status' => 'draft',
                ]);

                foreach ($template->exercises as $exercise) {
                    SessionExercise::create([
                        'workout_session_id' => $session->id,
                        'exercise_id' => $exercise->id,
                        'order' => $exercise->pivot->order,
                        'notes' => $exercise->pivot->notes,
                    ]);
                }
            });

            return response()->json([
                'message' => 'Séance créée en brouillon',
                'session_id' => $session->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur création session : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur interne serveur'], 500);
        }
    }


    public function getSessionWithExercises($id)
    {
        $session = WorkoutSession::with(['sessionExercises.exercise', 'workoutTemplate'])->findOrFail($id);

//        dd($session);

        return Inertia::render('WorkoutSessions/StartSession', compact('session'));
    }

    public function saveSets(StoreSetRequest $request, $sessionId)
    {
        try {
            $data = $request->validated()['sets']; // contient directement une liste plate de sets

            // Grouper les sets par session_exercise_id
            $grouped = collect($data)->groupBy('session_exercise_id');

            foreach ($grouped as $sessionExerciseId => $sets) {
                Set::where('session_exercise_id', $sessionExerciseId)->delete();

                foreach ($sets as $set) {
                    Set::create([
                        'session_exercise_id' => $sessionExerciseId,
                        'reps' => $set['reps'],
                        'weight' => $set['weight'] ?? null,
                        'rest_time' => $set['rest_time'] ?? null,
                    ]);
                }
            }

            return response()->json(['message' => 'Séries enregistrées avec succès']);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erreur serveur',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }



    public function startSession(StoreWorkoutSessionRequest $request, $id)
    {
        $session = WorkoutSession::findOrFail($id);

        $session->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json(['message' => 'Séance démarrée']);
    }

    public function showInProgress(WorkoutSession $session)
    {
        // Charger la session avec exercices et sets
        $session->load('sessionExercises.sets','sessionExercises.exercise', 'workoutTemplate');

        return Inertia::render('WorkoutSessions/InProgressSession', [
            'session' => $session,
        ]);
    }

    public function show(WorkoutSession $workoutSession)
    {
        return response()->json($workoutSession->load('sessionExercises.exercise'));
    }

    public function update(UpdateWorkoutSessionRequest $request, WorkoutSession $workoutSession)
    {
        $validated = $request->validated();
        $workoutSession->update($validated);

        return response()->json([
            'message' => 'Séance mise à jour',
            'session' => $workoutSession,
        ]);
    }

    public function destroy(WorkoutSession $workoutSession)
    {
        $workoutSession->delete();

        return response()->json(['message' => 'Séance supprimée']);
    }
}
