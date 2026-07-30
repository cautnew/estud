<?php

namespace App\Http\Requests;

use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudyPlanTopicStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [StudyPlanTopic::class, $this->route('studyPlanSubject')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var StudyPlanSubject $studyPlanSubject */
        $studyPlanSubject = $this->route('studyPlanSubject');

        return [
            'topic_id' => [
                'required',
                'integer',
                Rule::exists('topics', 'id')->where('subject_id', $studyPlanSubject->subject_id),
                Rule::unique('study_plan_topics', 'topic_id')->where('study_plan_subject_id', $studyPlanSubject->id),
            ],
        ];
    }
}
