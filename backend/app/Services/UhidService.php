<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UhidService
{
    public function generate(?int $tenantId = null): string
    {
        $year = (int) now()->format('Y');

        $nextNumber = DB::transaction(function () use ($tenantId, $year): int {
            $counter = DB::table('patient_uhid_counters')
                ->where('tenant_id', $tenantId)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                DB::table('patient_uhid_counters')->insert([
                    'tenant_id' => $tenantId,
                    'year' => $year,
                    'last_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return 1;
            }

            $next = (int) $counter->last_number + 1;

            DB::table('patient_uhid_counters')
                ->where('id', $counter->id)
                ->update([
                    'last_number' => $next,
                    'updated_at' => now(),
                ]);

            return $next;
        });

        return sprintf('HMS-%d-%06d', $year, $nextNumber);
    }
}
