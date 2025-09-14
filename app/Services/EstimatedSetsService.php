<?php

namespace App\Services;

use App\Models\Set;
use App\Models\SessionExercise;
use App\Models\WorkoutTemplateExercise;

class EstimatedSetsService
{
    /**
     * Génère des séries estimées basées sur les données du template
     */
    public function generateEstimatedSetsData(SessionExercise $sessionExercise, WorkoutTemplateExercise $templateExercise): array
    {
        $sets = [];
        
        for ($i = 1; $i <= $templateExercise->estimated_sets; $i++) {
            $sets[] = [
                'session_exercise_id' => $sessionExercise->id,
                'reps' => $templateExercise->estimated_reps,
                'weight' => $templateExercise->estimated_weight,
                'rest_time' => $templateExercise->estimated_rest_time,
                'order' => $i,
            ];
        }
        
        return $sets;
    }

    /**
     * Crée les séries estimées en base de données
     */
    public function createEstimatedSets(SessionExercise $sessionExercise, WorkoutTemplateExercise $templateExercise): void
    {
        $setsData = $this->generateEstimatedSetsData($sessionExercise, $templateExercise);
        
        foreach ($setsData as $setData) {
            Set::create($setData);
        }
    }

    /**
     * Génère des poids estimés intelligents basés sur l'historique de l'utilisateur
     */
    public function generateSmartEstimatedWeight(int $userId, int $exerciseId, int $defaultReps = 10): ?float
    {
        // Récupérer le poids moyen utilisé pour cet exercice par cet utilisateur
        $averageWeight = Set::whereHas('sessionExercise.workoutSession', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereHas('sessionExercise', function($query) use ($exerciseId) {
            $query->where('exercise_id', $exerciseId);
        })
        ->where('reps', '>=', $defaultReps - 2)
        ->where('reps', '<=', $defaultReps + 2)
        ->avg('weight');

        // Si on a des données historiques, utiliser le poids moyen
        if ($averageWeight) {
            return round($averageWeight, 2);
        }

        // Sinon, retourner null pour que l'utilisateur saisisse manuellement
        return null;
    }

    /**
     * Génère des temps de repos estimés basés sur le type d'exercice
     */
    public function generateEstimatedRestTime(string $exerciseName): int
    {
        $exerciseName = strtolower($exerciseName);
        
        // Exercices de force (temps de repos plus longs)
        if (str_contains($exerciseName, 'squat') || 
            str_contains($exerciseName, 'deadlift') || 
            str_contains($exerciseName, 'bench') ||
            str_contains($exerciseName, 'press')) {
            return 180; // 3 minutes
        }
        
        // Exercices de puissance
        if (str_contains($exerciseName, 'jump') || 
            str_contains($exerciseName, 'explosive') ||
            str_contains($exerciseName, 'plyometric')) {
            return 240; // 4 minutes
        }
        
        // Exercices d'endurance
        if (str_contains($exerciseName, 'cardio') || 
            str_contains($exerciseName, 'running') ||
            str_contains($exerciseName, 'cycling')) {
            return 30; // 30 secondes
        }
        
        // Exercices d'isolation (temps de repos moyens)
        if (str_contains($exerciseName, 'curl') || 
            str_contains($exerciseName, 'extension') ||
            str_contains($exerciseName, 'fly')) {
            return 90; // 1.5 minutes
        }
        
        // Par défaut
        return 60; // 1 minute
    }

    /**
     * Génère des répétitions estimées basées sur le type d'exercice
     */
    public function generateEstimatedReps(string $exerciseName): int
    {
        $exerciseName = strtolower($exerciseName);
        
        // Exercices de force (répétitions plus faibles)
        if (str_contains($exerciseName, 'squat') || 
            str_contains($exerciseName, 'deadlift') || 
            str_contains($exerciseName, 'bench') ||
            str_contains($exerciseName, 'press')) {
            return 8;
        }
        
        // Exercices d'endurance (répétitions plus élevées)
        if (str_contains($exerciseName, 'cardio') || 
            str_contains($exerciseName, 'running') ||
            str_contains($exerciseName, 'cycling')) {
            return 20;
        }
        
        // Exercices d'isolation
        if (str_contains($exerciseName, 'curl') || 
            str_contains($exerciseName, 'extension') ||
            str_contains($exerciseName, 'fly')) {
            return 12;
        }
        
        // Par défaut
        return 10;
    }

    /**
     * Génère le nombre de séries estimées basé sur le type d'exercice
     */
    public function generateEstimatedSets(string $exerciseName): int
    {
        $exerciseName = strtolower($exerciseName);
        
        // Exercices composés (plus de séries)
        if (str_contains($exerciseName, 'squat') || 
            str_contains($exerciseName, 'deadlift') || 
            str_contains($exerciseName, 'bench') ||
            str_contains($exerciseName, 'press')) {
            return 4;
        }
        
        // Exercices d'isolation (moins de séries)
        if (str_contains($exerciseName, 'curl') || 
            str_contains($exerciseName, 'extension') ||
            str_contains($exerciseName, 'fly')) {
            return 3;
        }
        
        // Par défaut
        return 3;
    }
}
