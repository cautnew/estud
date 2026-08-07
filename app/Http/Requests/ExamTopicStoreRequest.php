<?php

namespace App\Http\Requests;

use App\Models\ExamSubject;
use App\Models\ExamTopic;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamTopicStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [ExamTopic::class, $this->route('examSubject')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var ExamSubject $examSubject */
        $examSubject = $this->route('examSubject');

        return [
            'topic_id' => [
                'required',
                'integer',
                Rule::exists('topics', 'id')->where('subject_id', $examSubject->subject_id),
                Rule::unique('exam_topics', 'topic_id')->where('exam_subject_id', $examSubject->id),
            ],
        ];
    }
}
