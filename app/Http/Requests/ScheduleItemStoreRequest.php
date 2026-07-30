<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesScheduleItemDayFields;
use App\Models\Schedule;
use App\Models\ScheduleItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleItemStoreRequest extends FormRequest
{
    use ValidatesScheduleItemDayFields;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [ScheduleItem::class, $this->route('schedule')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Schedule $schedule */
        $schedule = $this->route('schedule');

        return array_merge([
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(
                    fn ($query) => $query->whereNull('owner_id')->orWhere('owner_id', $this->user()->id)
                ),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'priority' => ['sometimes', 'integer', 'min:0'],
        ], $this->dayFieldRules($schedule->type));
    }
}
