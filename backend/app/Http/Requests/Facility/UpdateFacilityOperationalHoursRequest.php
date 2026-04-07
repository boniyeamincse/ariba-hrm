<?php

namespace App\Http\Requests\Facility;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFacilityOperationalHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('facility.assignment.manage');
    }

    public function rules(): array
    {
        return [
            'hours' => 'required|array|min:1|max:7',
            'hours.*.day_of_week' => 'required|integer|between:0,6',
            'hours.*.is_closed' => 'required|boolean',
            'hours.*.opens_at' => 'nullable|date_format:H:i',
            'hours.*.closes_at' => 'nullable|date_format:H:i',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $hours = $this->input('hours', []);

            foreach ($hours as $index => $entry) {
                $isClosed = (bool) ($entry['is_closed'] ?? false);
                $opensAt = $entry['opens_at'] ?? null;
                $closesAt = $entry['closes_at'] ?? null;

                if ($isClosed) {
                    continue;
                }

                if (! $opensAt || ! $closesAt) {
                    $validator->errors()->add(
                        "hours.$index.opens_at",
                        'opens_at and closes_at are required when is_closed is false.'
                    );
                    continue;
                }

                if (strtotime($opensAt) >= strtotime($closesAt)) {
                    $validator->errors()->add(
                        "hours.$index.opens_at",
                        'The opens_at time must be earlier than closes_at.'
                    );
                }
            }
        });
    }
}
