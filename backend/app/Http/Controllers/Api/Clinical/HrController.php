<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\PayrollRun;
use App\Models\StaffProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrController extends Controller
{
    public function staff(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $data = StaffProfile::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->get();

        return response()->json(['data' => $data]);
    }

    public function createStaff(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'employee_code' => ['required', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:80'],
            'designation' => ['nullable', 'string', 'max:80'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'joined_at' => ['nullable', 'date'],
        ]);

        $staff = StaffProfile::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'user_id' => $data['user_id'] ?? null,
            'employee_code' => $data['employee_code'],
            'department' => $data['department'] ?? null,
            'designation' => $data['designation'] ?? null,
            'base_salary' => $data['base_salary'],
            'joined_at' => $data['joined_at'] ?? null,
            'status' => 'active',
        ]);

        return response()->json(['staff' => $staff], 201);
    }

    public function runPayroll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'period' => ['required', 'string', 'max:20'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');
        $allowance = (float) ($data['allowance'] ?? 0);
        $deduction = (float) ($data['deduction'] ?? 0);

        $run = PayrollRun::create([
            'tenant_id' => $tenantId,
            'period' => $data['period'],
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        $staffProfiles = StaffProfile::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->get();

        foreach ($staffProfiles as $staff) {
            $basic = (float) $staff->base_salary;
            $net = max(0, $basic + $allowance - $deduction);

            $run->items()->create([
                'staff_profile_id' => $staff->id,
                'basic' => $basic,
                'allowance' => $allowance,
                'deduction' => $deduction,
                'net_pay' => $net,
            ]);
        }

        return response()->json(['payroll_run' => $run->fresh('items')], 201);
    }
}
