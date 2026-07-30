<?php

namespace App\Http\Requests\Concerns;

use App\Enums\ScheduleType;

/**
 * Builds the `day_of_week`/`day_of_month` validation rules for a
 * ScheduleItem, based on the parent Schedule's recurrence `type`.
 *
 * Usage: `use ValidatesScheduleItemDayFields;` in a ScheduleItem-related
 * FormRequest, then merge `$this->dayFieldRules($schedule->type)` into
 * `rules()`.
 */
trait ValidatesScheduleItemDayFields
{
    /**
     * @return array<string, array<int, string>>
     */
    protected function dayFieldRules(ScheduleType $type): array
    {
        return match ($type) {
            ScheduleType::Daily => [
                'day_of_week' => ['prohibited'],
                'day_of_month' => ['prohibited'],
            ],
            ScheduleType::Weekly, ScheduleType::Biweekly => [
                'day_of_week' => ['required', 'integer', 'between:0,6'],
                'day_of_month' => ['prohibited'],
            ],
            ScheduleType::Monthly, ScheduleType::Quarterly => [
                'day_of_week' => ['prohibited'],
                'day_of_month' => ['required', 'integer', 'between:1,31'],
            ],
        };
    }
}
