<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesScheduleItemDayFields;
use App\Models\ScheduleItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleItemUpdateRequest extends FormRequest
{
    use ValidatesScheduleItemDayFields;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('scheduleItem'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var ScheduleItem $scheduleItem */
        $scheduleItem = $this->route('scheduleItem');

        return array_merge([
            'start_time' => ['sometimes', 'date_format:H:i'],
            'duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:1440'],
            'priority' => ['sometimes', 'integer', 'min:0'],
        ], $this->dayFieldRules($scheduleItem->schedule->type));
    }
}
