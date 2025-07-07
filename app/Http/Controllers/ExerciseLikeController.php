<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return response()->json([
            'liked' => $liked,
            'likes_count' => $exercise->likes()->count(),
        ]);
    }
}
