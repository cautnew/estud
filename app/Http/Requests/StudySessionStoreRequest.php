<?php

namespace App\Http\Requests;

use App\Models\StudySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudySessionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', StudySession::class);
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
            'date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ];
    }
}
