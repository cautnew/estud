<?php

namespace App\Http\Requests;

use App\Enums\GoalPeriod;
use App\Enums\GoalType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoalUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('goal'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::enum(GoalType::class)],
            'period' => ['sometimes', Rule::enum(GoalPeriod::class)],
            'target' => ['sometimes', 'numeric', 'min:0.5'],
            'subject_id' => [
                'nullable',
                'integer',
                Rule::exists('subjects', 'id')->where(
                    fn ($query) => $query->whereNull('owner_id')->orWhere('owner_id', $this->user()->id)
                ),
            ],
        ];
    }
}
