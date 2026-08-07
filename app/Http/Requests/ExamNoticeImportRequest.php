<?php

namespace App\Http\Requests;

use App\Models\ExamNotice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExamNoticeImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', ExamNotice::class);
    }

    /**
     * Decode the pasted JSON payload before validation runs.
     */
    protected function prepareForValidation(): void
    {
        $decoded = json_decode((string) $this->input('payload'), true);

        $this->merge(['data' => is_array($decoded) ? $decoded : null]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
            'data.name' => ['required', 'string', 'max:255'],
            'data.exam_board' => ['required', 'string', 'max:255'],
            'data.exam_date' => ['nullable', 'date'],
            'data.subjects' => ['required', 'array', 'min:1'],
            'data.subjects.*.name' => ['required', 'string', 'max:255'],
            'data.subjects.*.topics' => ['required', 'array', 'min:1'],
            'data.subjects.*.topics.*' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages, since "data" is an internal field name the
     * user never sees — errors should read as if about the pasted JSON.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'data.required' => 'O JSON informado é inválido ou está vazio.',
            'data.array' => 'O JSON informado é inválido.',
        ];
    }
}
