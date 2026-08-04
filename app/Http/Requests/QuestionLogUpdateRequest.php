<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class QuestionLogUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('questionLog'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject_id' => [
                'sometimes',
                'integer',
                Rule::exists('subjects', 'id')->where(
                    fn ($query) => $query->whereNull('owner_id')->orWhere('owner_id', $this->user()->id)
                ),
            ],
            'topic_id' => [
                'nullable',
                'integer',
                Rule::exists('topics', 'id')->where(
                    fn ($query) => $query->where('subject_id', $this->input('subject_id') ?? $this->route('questionLog')->subject_id)
                ),
            ],
            'date' => ['sometimes', 'date'],
            'total' => ['sometimes', 'integer', 'min:1'],
            'correct' => ['sometimes', 'integer', 'min:0'],
            'incorrect' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $questionLog = $this->route('questionLog');

                $total = $this->input('total', $questionLog->total);
                $correct = $this->input('correct', $questionLog->correct);
                $incorrect = $this->input('incorrect', $questionLog->incorrect);

                if ($correct + $incorrect > $total) {
                    $validator->errors()->add('total', 'A soma de corretas e incorretas não pode ultrapassar o total de questões resolvidas.');
                }
            },
        ];
    }
}
