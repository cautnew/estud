<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesScheduleItemDayFields;
use App\Models\ScheduleItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleItemMoveRequest extends FormRequest
{
    use ValidatesScheduleItemDayFields;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('move', $this->route('scheduleItem'));
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
            'start_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'priority' => ['required', 'integer', 'min:0'],
        ], $this->dayFieldRules($scheduleItem->schedule->type));
    }
}
