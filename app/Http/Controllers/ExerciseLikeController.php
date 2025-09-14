<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ExerciseLikeController extends Controller
{
    public function toggleLike(Exercise $exercise)
    {
        $user = auth()->user();
        $existingLike = $exercise->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            $exercise->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }

        // Invalider le cache des exercices pour cet utilisateur
        Cache::forget('exercises_with_likes_' . $user->id);
        Cache::forget('exercises_with_likes_guest');

        return response()->json([
            'liked' => $liked,
            'likes_count' => $exercise->likes()->count(),
        ]);
    }

    /**
     * Afficher la liste des exercices likés par l'utilisateur connecté
     */
    public function index()
    {
        $user = auth()->user();
        
        $likedExercises = $user->likedExercises()
            ->with(['muscleTargets', 'likes'])
            ->orderBy('exercise_likes.created_at', 'desc')
            ->get()
            ->map(function ($exercise) use ($user) {
                $exerciseArray = $exercise->toArray();
                $exerciseArray['is_liked'] = true; // Forcément true puisque c'est la liste des likés
                $exerciseArray['likes_count'] = $exercise->likes()->count();
                return $exerciseArray;
            });

        return Inertia::render('Exercises/Liked', [
            'likedExercises' => $likedExercises,
        ]);
    }

    /**
     * Récupérer les exercices likés via API
     */
    public function getLikedExercises()
    {
        $user = auth()->user();
        
        $likedExercises = $user->likedExercises()
            ->with(['muscleTargets'])
            ->orderBy('exercise_likes.created_at', 'desc')
            ->get();

        return response()->json($likedExercises);
    }
}
