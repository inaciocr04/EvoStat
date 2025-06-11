<?php

namespace App\Http\Controllers;

use App\Models\SessionExercise;
use App\Models\Set;
use App\Http\Requests\StoreSetRequest;
use App\Http\Requests\UpdateSetRequest;
use Illuminate\Support\Facades\Log;

class SetController extends Controller
{
    public function saveSets(StoreSetRequest $request)
    {
        $data = $request->validated();

        if (!isset($data['sets'])) {
            return response()->json(['error' => 'Le champ "sets" est manquant.'], 422);
        }

        $newSetIds = [];

        foreach ($data['sets'] as $setData) {
            if (!empty($setData['id'])) {
                Set::where('id', $setData['id'])->update([
                    'reps' => $setData['reps'],
                    'weight' => $setData['weight'],
                    'rest_time' => $setData['rest_time'],
//                    'done' => $setData['done'] ?? false,
                ]);
            } else {
                $newSet = Set::create([
                    'session_exercise_id' => $setData['session_exercise_id'],
                    'reps' => $setData['reps'],
                    'weight' => $setData['weight'],
                    'rest_time' => $setData['rest_time'],
//                    'done' => $setData['done'] ?? false,
                ]);
                $newSetIds[] = $newSet->id;
            }
        }

        return response()->json([
            'message' => 'Sets saved successfully',
            'new_set_ids' => $newSetIds,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSetRequest $request, SessionExercise $sessionExercise)
    {
        $set = $sessionExercise->sets()->create([
            'reps' => $request->reps ?? 8,
            'weight' => $request->weight ?? 0,
            'rest_time' => $request->rest_time ?? 60,
            'done' => $request->done ?? false,
        ]);

        return response()->json(['id' => $set->id]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Set $set)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Set $set)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSetRequest $request, Set $set)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Set $set)
    {
        //
    }
}
