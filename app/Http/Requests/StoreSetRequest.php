<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSetRequest extends FormRequest
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
            'sets' => ['required', 'array', 'min:1'],
            'sets.*.id' => ['nullable', 'numeric', 'exists:sets,id'],
            'sets.*.session_exercise_id' => ['required', 'exists:session_exercises,id'],
            'sets.*.reps' => ['required', 'integer', 'min:1'],
            'sets.*.weight' => ['nullable', 'numeric', 'min:0'],
            'sets.*.rest_time' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
