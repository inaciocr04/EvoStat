<?php

namespace App\Http\Controllers;

use App\Models\ScheduledWorkout;
use App\Models\WorkoutTemplate;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Carbon\Carbon;

class PlanningController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->get('month', now()->format('Y-m'));
        
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth();
        
        // Récupérer les séances programmées du mois
        $scheduledWorkouts = ScheduledWorkout::with(['workoutTemplate', 'workoutSession'])
            ->where('user_id', $user->id)
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();
        
        // Récupérer TOUTES les séances réalisées du mois (programmées ou non)
        $completedSessions = WorkoutSession::with(['workoutTemplate', 'sessionExercises.sets'])
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->orderBy('completed_at')
            ->get();
        
        // Récupérer les templates disponibles pour programmer
        $workoutTemplates = WorkoutTemplate::where('user_id', $user->id)->get();
        
        // Statistiques du mois
        $stats = [
            'total_scheduled' => $scheduledWorkouts->count(),
            'completed' => $scheduledWorkouts->where('status', 'completed')->count(),
            'skipped' => $scheduledWorkouts->where('status', 'skipped')->count(),
            'pending' => $scheduledWorkouts->where('status', 'scheduled')->count(),
            'total_completed_sessions' => $completedSessions->count(),
        ];
        
        // Récupérer l'historique des séances terminées (toutes les séances, pas seulement programmées)
        $historyWorkouts = WorkoutSession::with(['workoutTemplate'])
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'workout_template' => $session->workoutTemplate,
                    'scheduled_date' => $session->completed_at ? $session->completed_at->format('Y-m-d') : null,
                    'scheduled_time' => $session->completed_at ? $session->completed_at->format('H:i') : null,
                    'status' => 'completed',
                    'workout_session_id' => $session->id,
                    'notes' => null,
                    'workout_session' => $session,
                ];
            });
        
        return Inertia::render('Planning', [
            'scheduledWorkouts' => $scheduledWorkouts,
            'completedSessions' => $completedSessions,
            'workoutTemplates' => $workoutTemplates,
            'currentMonth' => $month,
            'stats' => $stats,
            'historyWorkouts' => $historyWorkouts,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'workout_template_id' => 'required|exists:workout_templates,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $scheduledWorkout = ScheduledWorkout::create([
            'user_id' => $request->user()->id,
            'workout_template_id' => $request->workout_template_id,
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'notes' => $request->notes,
            'status' => 'scheduled',
        ]);
        
        return redirect()->route('planning.index')
            ->with('success', 'Séance programmée avec succès !');
    }
    
    public function update(Request $request, ScheduledWorkout $scheduledWorkout)
    {
        $this->authorize('update', $scheduledWorkout);
        
        $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $scheduledWorkout->update($request->only(['scheduled_date', 'scheduled_time', 'notes']));
        
        return redirect()->route('planning.index')
            ->with('success', 'Séance mise à jour avec succès !');
    }
    
    public function destroy(ScheduledWorkout $scheduledWorkout)
    {
        $this->authorize('delete', $scheduledWorkout);
        
        $scheduledWorkout->delete();
        
        return redirect()->route('planning.index')
            ->with('success', 'Séance supprimée avec succès !');
    }
    
    public function markCompleted(Request $request, ScheduledWorkout $scheduledWorkout)
    {
        $this->authorize('update', $scheduledWorkout);
        
        $request->validate([
            'workout_session_id' => 'required|exists:workout_sessions,id',
        ]);
        
        $scheduledWorkout->update([
            'status' => 'completed',
            'workout_session_id' => $request->workout_session_id,
        ]);
        
        return redirect()->route('planning.index')
            ->with('success', 'Séance marquée comme terminée !');
    }
    
    public function markSkipped(ScheduledWorkout $scheduledWorkout)
    {
        $this->authorize('update', $scheduledWorkout);
        
        $scheduledWorkout->update(['status' => 'skipped']);
        
        return redirect()->route('planning.index')
            ->with('success', 'Séance marquée comme ignorée !');
    }
    
    public function startFromScheduled(ScheduledWorkout $scheduledWorkout)
    {
        $this->authorize('update', $scheduledWorkout);
        
        // Créer une nouvelle session depuis le template
        $session = WorkoutSession::create([
            'user_id' => $scheduledWorkout->user_id,
            'workout_template_id' => $scheduledWorkout->workout_template_id,
            'status' => 'draft',
        ]);
        
        // Copier les exercices du template
        $template = $scheduledWorkout->workoutTemplate;
        foreach ($template->workoutTemplateExercises as $templateExercise) {
            \App\Models\SessionExercise::create([
                'workout_session_id' => $session->id,
                'exercise_id' => $templateExercise->exercise_id,
                'order' => $templateExercise->order,
                'notes' => $templateExercise->notes,
            ]);
        }
        
        return redirect()->route('sessions.inprogress', $session)
            ->with('success', 'Séance démarrée !');
    }
    
    public function createRecurringWorkouts(Request $request)
    {
        $request->validate([
            'workout_template_id' => 'required|exists:workout_templates,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'indefinite_end' => 'boolean',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:1,7', // 1=Lundi, 7=Dimanche
            'scheduled_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $user = $request->user();
        $startDate = Carbon::parse($request->start_date);
        
        // Si indéfini, on programme pour 6 mois par défaut
        if ($request->indefinite_end) {
            $endDate = $startDate->copy()->addMonths(6);
        } else {
            $endDate = Carbon::parse($request->end_date);
        }
        
        $daysOfWeek = $request->days_of_week;
        
        $createdCount = 0;
        
        // Parcourir chaque semaine entre start_date et end_date
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            foreach ($daysOfWeek as $dayOfWeek) {
                // Trouver le prochain jour de la semaine spécifié
                $targetDate = $currentDate->copy()->next($dayOfWeek);
                
                if ($targetDate->lte($endDate)) {
                    ScheduledWorkout::create([
                        'user_id' => $user->id,
                        'workout_template_id' => $request->workout_template_id,
                        'scheduled_date' => $targetDate->format('Y-m-d'),
                        'scheduled_time' => $request->scheduled_time,
                        'notes' => $request->notes,
                        'status' => 'scheduled',
                    ]);
                    $createdCount++;
                }
            }
            $currentDate->addWeek();
        }
        
        $message = $request->indefinite_end 
            ? "Séances récurrentes créées avec succès ! ({$createdCount} séances programmées pour 6 mois)"
            : "Séances récurrentes créées avec succès ! ({$createdCount} séances programmées)";
        
        return redirect()->route('planning.index')->with('success', $message);
    }
    
    public function bulkDelete(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'workout_ids' => 'required|array|min:1',
            'workout_ids.*' => 'integer|exists:scheduled_workouts,id',
        ]);
        
        $deletedCount = ScheduledWorkout::where('user_id', $user->id)
            ->whereIn('id', $request->workout_ids)
            ->where('status', 'scheduled') // Seulement les séances programmées
            ->delete();
        
        return redirect()->route('planning.index')
            ->with('success', "{$deletedCount} séances programmées supprimées avec succès !");
    }
}
