<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudyPlanUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('studyPlan'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'exam_board' => ['required', 'string', 'max:255'],
            'goal' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'exam_date' => ['nullable', 'date'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
