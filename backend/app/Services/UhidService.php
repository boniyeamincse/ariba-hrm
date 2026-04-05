<?php

namespace App\Services;

use App\Models\Patient;

class UhidService
{
    public function generate(): string
    {
        $date = now()->format('Ymd');
        $sequence = str_pad((string) (Patient::query()->count() + 1), 6, '0', STR_PAD_LEFT);

        return 'UHID-'.$date.'-'.$sequence;
    }
}
