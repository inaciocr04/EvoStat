<?php

namespace App\Console\Commands;

use App\Models\WorkoutTemplate;
use App\Models\WorkoutTemplateExercise;
use App\Models\WorkoutSession;
use App\Models\SessionExercise;
use App\Models\Set;
use App\Services\EstimatedSetsService;
use Illuminate\Console\Command;

class TestEstimatedSets extends Command
{
    protected $signature = 'test:estimated-sets';
    protected $description = 'Teste le système de séries estimées';

    public function handle()
    {
        $this->info('🧪 Test du système de séries estimées...');

        // Vérifier qu'il y a des templates avec des exercices
        $templates = WorkoutTemplate::with('workoutTemplateExercises.exercise')->get();
        
        if ($templates->isEmpty()) {
            $this->warn('❌ Aucun template trouvé. Créez d\'abord un template avec des exercices.');
            return;
        }

        $template = $templates->first();
        $this->info("📋 Template utilisé: {$template->name}");

        // Vérifier les exercices du template
        $templateExercises = $template->workoutTemplateExercises;
        $this->info("🏋️ Nombre d'exercices dans le template: {$templateExercises->count()}");

        foreach ($templateExercises as $templateExercise) {
            $exercise = $templateExercise->exercise;
            $this->info("  - {$exercise->name}");
            $this->info("    Séries estimées: {$templateExercise->estimated_sets}");
            $this->info("    Répétitions estimées: {$templateExercise->estimated_reps}");
            $this->info("    Poids estimé: " . ($templateExercise->estimated_weight ?? 'Non défini'));
            $this->info("    Temps de repos estimé: {$templateExercise->estimated_rest_time}s");
        }

        // Créer une session de test
        $this->info("\n🎯 Création d'une session de test...");
        
        $session = WorkoutSession::create([
            'user_id' => 1, // Assumons qu'il y a un utilisateur avec l'ID 1
            'workout_template_id' => $template->id,
            'status' => 'draft',
        ]);

        $this->info("✅ Session créée avec l'ID: {$session->id}");

        // Créer les exercices de session et les séries estimées
        $estimatedSetsService = new EstimatedSetsService();
        
        foreach ($templateExercises as $templateExercise) {
            $sessionExercise = SessionExercise::create([
                'workout_session_id' => $session->id,
                'exercise_id' => $templateExercise->exercise_id,
                'order' => $templateExercise->order,
                'notes' => $templateExercise->notes,
            ]);

            // Générer les séries estimées
            $estimatedSetsService->createEstimatedSets($sessionExercise, $templateExercise);
            
            $this->info("✅ Séries créées pour: {$templateExercise->exercise->name}");
        }

        // Vérifier les séries créées
        $session->load(['sessionExercises.sets', 'sessionExercises.exercise']);
        
        $this->info("\n📊 Vérification des séries créées:");
        foreach ($session->sessionExercises as $sessionExercise) {
            $setsCount = $sessionExercise->sets->count();
            $this->info("  - {$sessionExercise->exercise->name}: {$setsCount} séries");
            
            foreach ($sessionExercise->sets as $set) {
                $this->info("    Série: {$set->reps} reps @ {$set->weight}kg (repos: {$set->rest_time}s)");
            }
        }

        $this->info("\n🎉 Test terminé avec succès !");
        $this->info("💡 Vous pouvez maintenant tester l'interface en créant une session depuis ce template.");
        
        return 0;
    }
}