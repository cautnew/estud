<?php

namespace App\Http\Requests;

use App\Models\ExamNotice;
use App\Models\ExamSubject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamSubjectStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [ExamSubject::class, $this->route('examNotice')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var ExamNotice $examNotice */
        $examNotice = $this->route('examNotice');

        return [
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id'),
                Rule::unique('exam_subjects', 'subject_id')->where('exam_notice_id', $examNotice->id),
            ],
        ];
    }
}
