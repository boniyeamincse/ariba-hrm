<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\TelemedicineSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function slots(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $data = AppointmentSlot::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderByDesc('slot_date')
            ->orderBy('start_time')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function createSlot(Request $request): JsonResponse
    {
        $data = $request->validate([
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'slot_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'max_patients' => ['nullable', 'integer', 'min:1'],
        ]);

        $slot = AppointmentSlot::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'doctor_id' => $data['doctor_id'] ?? null,
            'slot_date' => $data['slot_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'max_patients' => $data['max_patients'] ?? 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        return response()->json(['slot' => $slot], 201);
    }

    public function book(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'appointment_slot_id' => ['required', 'integer', 'exists:appointment_slots,id'],
            'visit_mode' => ['nullable', 'in:in_person,telemedicine'],
            'notes' => ['nullable', 'string'],
        ]);

        $slot = AppointmentSlot::findOrFail($data['appointment_slot_id']);
        if ($slot->booked_count >= $slot->max_patients) {
            return response()->json(['message' => 'Slot is full.'], 422);
        }

        $appointment = Appointment::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'] ?? $slot->doctor_id,
            'appointment_slot_id' => $slot->id,
            'scheduled_at' => $slot->slot_date->format('Y-m-d').' '.$slot->start_time,
            'status' => 'scheduled',
            'visit_mode' => $data['visit_mode'] ?? 'in_person',
            'notes' => $data['notes'] ?? null,
        ]);

        $slot->increment('booked_count');

        return response()->json(['appointment' => $appointment], 201);
    }

    public function createTelemedicineSession(Request $request, Appointment $appointment): JsonResponse
    {
        $meetingId = 'tm-'.now()->format('YmdHis').'-'.$appointment->id;
        $url = 'https://meet.example.com/'.$meetingId;

        $session = TelemedicineSession::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'appointment_id' => $appointment->id,
            'provider' => 'jitsi',
            'meeting_id' => $meetingId,
            'meeting_url' => $url,
            'status' => 'created',
        ]);

        $appointment->update(['visit_mode' => 'telemedicine']);

        return response()->json(['session' => $session], 201);
    }
}
