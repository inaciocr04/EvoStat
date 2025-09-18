<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ExerciseRatingController;
use App\Http\Controllers\HistoryAndStatsController;
use App\Http\Controllers\ExerciseLikeController;
use App\Http\Controllers\MuscleCategoryController;
use App\Http\Controllers\MuscleTargetController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilsController;
use App\Http\Controllers\SetController;
use App\Http\Controllers\WorkoutSessionController;
use App\Http\Controllers\WorkoutTemplateController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [ProfilsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/profils', [ProfilsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('profils');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Routes publiques pour utilisateurs connectés et vérifiés
    Route::get('/exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::get('/exercises/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');
    Route::post('/exercises/{exercise}/like', [ExerciseLikeController::class, 'toggleLike'])->name('exercises.toggleLike');
    Route::get('/exercises/liked', [ExerciseLikeController::class, 'index'])->name('exercises.liked');
    Route::get('/api/exercises/liked', [ExerciseLikeController::class, 'getLikedExercises'])->name('api.exercises.liked');
    Route::get('/exercises/{exercise}/rating', [ExerciseRatingController::class, 'getRating']);
    Route::post('/exercises/{exercise}/rate', [ExerciseRatingController::class, 'storeOrUpdate'])->name('exercises.rate');
    
    Route::resource('workout-templates', WorkoutTemplateController::class);
    Route::post('/workout-templates/store-and-start', [WorkoutTemplateController::class, 'storeAndStart'])
        ->name('workout-templates.store-and-start');
    Route::post('/workout-templates/{template}/start', [WorkoutSessionController::class, 'startFromTemplate'])
        ->name('workout_templates.start');

    Route::post('/sessions/create-from-template', [WorkoutSessionController::class, 'createSessionFromTemplate']);
    Route::get('/sessions/{id}', [WorkoutSessionController::class, 'getSessionWithExercises']);
    Route::post('/sessions/{id}/sets', [WorkoutSessionController::class, 'saveSets']);
    Route::post('/sessions/{id}/start', [WorkoutSessionController::class, 'startSession']);
    Route::get('/sessions/inprogress/{session}', [WorkoutSessionController::class, 'showInProgress'])
        ->name('sessions.inprogress');
    Route::post('/workout-sessions/from-template', [WorkoutSessionController::class, 'createSessionFromTemplate']);
    Route::post('/sets/save', [SetController::class, 'saveSets']);
    Route::post('/sessions/{session}/exercises/{session_exercise}/sets', [SetController::class, 'store'])
        ->name('session.sets.store');

    Route::resource('sessions', WorkoutSessionController::class);

    Route::get('/stats', [HistoryAndStatsController::class, 'getStats'])->name('stats');
    
    // Routes pour le planning
    Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
    Route::post('/planning', [PlanningController::class, 'store'])->name('planning.store');
    Route::post('/planning/recurring', [PlanningController::class, 'createRecurringWorkouts'])->name('planning.recurring');
    Route::post('/planning/bulk-delete', [PlanningController::class, 'bulkDelete'])->name('planning.bulk-delete');
    Route::put('/planning/{scheduledWorkout}', [PlanningController::class, 'update'])->name('planning.update');
    Route::delete('/planning/{scheduledWorkout}', [PlanningController::class, 'destroy'])->name('planning.destroy');
    Route::post('/planning/{scheduledWorkout}/complete', [PlanningController::class, 'markCompleted'])->name('planning.complete');
    Route::post('/planning/{scheduledWorkout}/skip', [PlanningController::class, 'markSkipped'])->name('planning.skip');
    Route::post('/planning/{scheduledWorkout}/start', [PlanningController::class, 'startFromScheduled'])->name('planning.start');
    
    // Statistiques (accessible à tous les utilisateurs connectés)
    Route::get('/statistics', [App\Http\Controllers\StatsController::class, 'index'])->name('statistics');
    
    // Routes ADMIN uniquement
    Route::middleware('role:admin')->group(function () {
        // Gestion des exercices (admin seulement)
        Route::get('/exercises/create', [ExerciseController::class, 'create'])->name('exercises.create');
        Route::post('/exercises', [ExerciseController::class, 'store'])->name('exercises.store');
        Route::get('/exercises/{exercise}/edit', [ExerciseController::class, 'edit'])->name('exercises.edit');
        Route::put('/exercises/{exercise}', [ExerciseController::class, 'update'])->name('exercises.update');
        Route::delete('/exercises/{exercise}', [ExerciseController::class, 'destroy'])->name('exercises.destroy');
        
        // Gestion des muscles et catégories (admin seulement)
        Route::resource('muscleTargets', MuscleTargetController::class);
        Route::resource('muscleCategories', MuscleCategoryController::class);
    });
});


require __DIR__.'/auth.php';
