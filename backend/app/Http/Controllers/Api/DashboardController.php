<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $tenantId = $request->get('tenant_id');
        $user = $request->user();

        $stats = [
            'tasks' => [
                'total' => Task::where('tenant_id', $tenantId)->count(),
                'pending' => Task::where('tenant_id', $tenantId)->where('status', 'todo')->count(),
                'in_progress' => Task::where('tenant_id', $tenantId)->where('status', 'in_progress')->count(),
                'completed' => Task::where('tenant_id', $tenantId)->where('status', 'completed')->count(),
            ],
            'patients' => [
                'total' => Patient::where('tenant_id', $tenantId)->count(),
                'new_today' => Patient::where('tenant_id', $tenantId)->whereDate('created_at', now())->count(),
            ],
            'appointments' => [
                'today' => Appointment::where('tenant_id', $tenantId)->whereDate('appointment_date', now())->count(),
                'pending' => Appointment::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            ]
        ];

        // Role-specific stats
        if ($user->hasRole('doctor')) {
            $stats['role_specific'] = [
                'my_appointments_today' => Appointment::where('tenant_id', $tenantId)
                    ->where('doctor_id', $user->id)
                    ->whereDate('appointment_date', now())
                    ->count(),
                'my_pending_tasks' => Task::where('tenant_id', $tenantId)
                    ->where('assigned_to_id', $user->id)
                    ->where('status', 'todo')
                    ->count(),
            ];
        }

        return response()->json($stats);
    }
}
