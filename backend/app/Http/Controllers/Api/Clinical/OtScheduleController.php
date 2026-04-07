<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\SurgerySchedule;
use Illuminate\Http\Request;

class OtScheduleController extends Controller
{
    public function index()
    {
        $schedules = SurgerySchedule::query()
            ->with(['patient', 'operationTheater', 'surgery', 'primarySurgeon', 'anesthesiologist'])
            ->latest()
            ->get();

        return response()->json(['data' => $schedules]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'patient_visit_id' => 'nullable|exists:patient_visits,id',
            'operation_theater_id' => 'required|exists:operation_theaters,id',
            'surgery_id' => 'required|exists:surgeries,id',
            'scheduled_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'status' => 'nullable|string|in:scheduled,in_progress,recovery,completed,cancelled',
            'primary_surgeon_id' => 'nullable|exists:users,id',
            'anesthesiologist_id' => 'nullable|exists:users,id',
            'anesthesia_type' => 'nullable|string|max:255',
            'pre_op_notes' => 'nullable|string',
            'surgery_notes' => 'nullable|string',
        ]);

        if (!isset($validated['status'])) {
            $validated['status'] = 'scheduled';
        }

        $schedule = SurgerySchedule::create($validated);

        return response()->json([
            'message' => 'Surgery scheduled successfully.',
            'schedule' => $schedule->load(['patient', 'operationTheater', 'surgery']),
        ], 201);
    }

    public function updateStatus(Request $request, SurgerySchedule $schedule)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:scheduled,in_progress,recovery,completed,cancelled',
        ]);

        $schedule->update($validated);

        if ($validated['status'] === 'in_progress') {
            $schedule->operationTheater()->update(['status' => 'occupied']);
        } elseif (in_array($validated['status'], ['recovery', 'completed', 'cancelled'])) {
            $schedule->operationTheater()->update(['status' => 'available']);
        }

        return response()->json([
            'message' => 'Schedule status updated.',
            'schedule' => $schedule,
        ]);
    }

    public function updateAnesthesia(Request $request, SurgerySchedule $schedule)
    {
        $validated = $request->validate([
            'anesthesiologist_id' => 'required|exists:users,id',
            'anesthesia_type' => 'required|string|max:255',
            'surgery_notes' => 'nullable|string',
        ]);

        $schedule->update($validated);

        return response()->json([
            'message' => 'Anesthesia details updated.',
            'schedule' => $schedule,
        ]);
    }
}
