<?php

namespace App\Http\Requests;

use App\Models\QuestionLog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class QuestionLogStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', QuestionLog::class);
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
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(
                    fn ($query) => $query->whereNull('owner_id')->orWhere('owner_id', $this->user()->id)
                ),
            ],
            'topic_id' => [
                'nullable',
                'integer',
                Rule::exists('topics', 'id')->where(
                    fn ($query) => $query->where('subject_id', $this->input('subject_id'))
                ),
            ],
            'date' => ['required', 'date'],
            'total' => ['required', 'integer', 'min:1'],
            'correct' => ['required', 'integer', 'min:0'],
            'incorrect' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('correct', 0) + $this->input('incorrect', 0) > $this->input('total', 0)) {
                    $validator->errors()->add('total', 'A soma de corretas e incorretas não pode ultrapassar o total de questões resolvidas.');
                }
            },
        ];
    }
}
