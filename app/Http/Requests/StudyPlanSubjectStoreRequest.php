<?php

namespace App\Http\Requests;

use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudyPlanSubjectStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [StudyPlanSubject::class, $this->route('studyPlan')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var StudyPlan $studyPlan */
        $studyPlan = $this->route('studyPlan');

        return [
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(
                    fn ($query) => $query->whereNull('owner_id')->orWhere('owner_id', $this->user()->id)
                ),
                Rule::unique('study_plan_subjects', 'subject_id')->where('study_plan_id', $studyPlan->id),
            ],
        ];
    }
}
