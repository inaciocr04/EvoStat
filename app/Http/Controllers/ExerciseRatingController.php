<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseRating;
use Illuminate\Http\Request;

class ExerciseRatingController extends Controller
{
//    public function storeOrUpdate(Request $request, $exerciseId)
//    {
//        $user = auth()->user();
//        $ratingValue = (float) $request->input('rating');
//
//        // Valide la note
//        if ($ratingValue < 1 || $ratingValue > 5) {
//            return response()->json(['message' => 'Note invalide'], 422);
//        }
//
//        ExerciseRating::updateOrCreate(
//            ['user_id' => $user->id, 'exercise_id' => $exerciseId],
//            ['rating' => $ratingValue]
//        );
//
//        $average = ExerciseRating::where('exercise_id', $exerciseId)->avg('rating') ?? 0;
//
//        // Retourne la moyenne arrondie à 1 décimale
//        return response()->json(['average' => round($average, 1)]);
//    }
    public function storeOrUpdate(Request $request, Exercise $exercise)
    {
        $request->validate([
            'rating' => 'required|numeric|min:0|max:5',
        ]);

        $user = auth()->user();

        if ($request->rating == 0) {
            $exercise->ratings()->where('user_id', $user->id)->delete();
        } else {
            $exercise->ratings()->updateOrCreate(
                ['user_id' => $user->id],
                ['rating' => $request->rating]
            );
        }

        $average = round($exercise->ratings()->avg('rating') ?? 0, 1);

        return response()->json([
            'success' => true,
            'average' => $average,
        ]);
    }

    public function getRating(Exercise $exercise)
    {
        $user = auth()->user();

        $userRating = $exercise->ratings()
            ->where('user_id', $user->id)
            ->value('rating');

        $average = round($exercise->ratings()->avg('rating') ?? 0, 1);

        return response()->json([
            'user_rating' => $userRating ?? 0,
            'average' => $average,
        ]);
    }

}
