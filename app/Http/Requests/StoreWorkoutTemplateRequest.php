<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'exercises' => 'required|array|min:1',
            'exercises.*.exercise_id' => 'required|exists:exercises,id',
            'exercises.*.order' => 'required|integer|min:0',
            'exercises.*.notes' => 'nullable|string',
            'exercises.*.estimated_sets' => 'required|integer|min:1|max:10',
            'exercises.*.estimated_reps' => 'required|integer|min:1|max:50',
            'exercises.*.estimated_weight' => 'nullable|numeric|min:0',
            'exercises.*.estimated_rest_time' => 'required|numeric|min:0.1|max:10',
        ];
    }
}
