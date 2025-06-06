<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\MuscleTargetController;
use App\Http\Controllers\ProfileController;
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
    Route::post('/workout-templates/{template}/start', [WorkoutSessionController::class, 'startFromTemplate'])
        ->name('workout_templates.start');
});
Route::resource('workout-templates', WorkoutTemplateController::class);


require __DIR__.'/auth.php';
