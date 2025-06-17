<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\HistoryAndStatsController;
use App\Http\Controllers\MuscleCategoryController;
use App\Http\Controllers\MuscleTargetController;
use App\Http\Controllers\ProfileController;
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

Route::get('/profils', function () {
    return Inertia::render('Profils');
})->middleware(['auth', 'verified'])->name('profils');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::resource('exercises', ExerciseController::class);
    Route::resource('muscleTargets', MuscleTargetController::class);
    Route::resource('workout-templates', WorkoutTemplateController::class);
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
    Route::resource('muscleCategories', MuscleCategoryController::class);

});


require __DIR__.'/auth.php';
