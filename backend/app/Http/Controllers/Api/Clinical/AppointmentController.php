<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\TelemedicineSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function listSlots(Request $request): JsonResponse
    {
        return $this->slots($request);
    }

    public function slots(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $data = AppointmentSlot::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($request->filled('doctor_id'), fn ($query) => $query->where('doctor_id', $request->integer('doctor_id')))
            ->when($request->filled('slot_date'), fn ($query) => $query->whereDate('slot_date', $request->date('slot_date')))
            ->where('is_active', true)
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

        $tenantId = $request->attributes->get('tenant_id');

        $appointment = DB::transaction(function () use ($data, $tenantId): Appointment {
            $slot = AppointmentSlot::query()
                ->lockForUpdate()
                ->findOrFail($data['appointment_slot_id']);

            if ($slot->tenant_id !== null && (int) $slot->tenant_id !== (int) $tenantId) {
                abort(404, 'Slot not found.');
            }

            if (! $slot->is_active || $slot->booked_count >= $slot->max_patients) {
                abort(422, 'Slot is full or inactive.');
            }

            $appointment = Appointment::create([
                'tenant_id' => $tenantId,
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'] ?? $slot->doctor_id,
                'appointment_slot_id' => $slot->id,
                'scheduled_at' => $slot->slot_date->format('Y-m-d').' '.$slot->start_time,
                'status' => 'scheduled',
                'visit_mode' => $data['visit_mode'] ?? 'in_person',
                'notes' => $data['notes'] ?? null,
            ]);

            $slot->increment('booked_count');

            return $appointment;
        });

        return response()->json(['appointment' => $appointment], 201);
    }

    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');
        if ($appointment->tenant_id !== null && (int) $appointment->tenant_id !== (int) $tenantId) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        if ($appointment->status === 'cancelled') {
            return response()->json(['message' => 'Appointment already cancelled.'], 422);
        }

        DB::transaction(function () use ($appointment, $data): void {
            $appointment->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $data['reason'] ?? null,
            ]);

            if ($appointment->appointment_slot_id) {
                AppointmentSlot::query()->whereKey($appointment->appointment_slot_id)->decrement('booked_count');
            }
        });

        return response()->json([
            'message' => 'Appointment cancelled.',
            'appointment' => $appointment->fresh(),
        ]);
    }

    public function reschedule(Request $request, Appointment $appointment): JsonResponse
    {
        $data = $request->validate([
            'appointment_slot_id' => ['required', 'integer', 'exists:appointment_slots,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');
        if ($appointment->tenant_id !== null && (int) $appointment->tenant_id !== (int) $tenantId) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        $rescheduled = DB::transaction(function () use ($appointment, $data, $tenantId): Appointment {
            $nextSlot = AppointmentSlot::query()->lockForUpdate()->findOrFail($data['appointment_slot_id']);
            if ($nextSlot->tenant_id !== null && (int) $nextSlot->tenant_id !== (int) $tenantId) {
                abort(404, 'Slot not found.');
            }

            if (! $nextSlot->is_active || $nextSlot->booked_count >= $nextSlot->max_patients) {
                abort(422, 'Requested slot is full or inactive.');
            }

            $appointment->update([
                'status' => 'rescheduled',
                'notes' => trim(($appointment->notes ? $appointment->notes."\n" : '').'Reschedule reason: '.($data['reason'] ?? 'N/A')),
            ]);

            if ($appointment->appointment_slot_id) {
                AppointmentSlot::query()->whereKey($appointment->appointment_slot_id)->decrement('booked_count');
            }

            $nextSlot->increment('booked_count');

            return Appointment::create([
                'tenant_id' => $tenantId,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id ?? $nextSlot->doctor_id,
                'appointment_slot_id' => $nextSlot->id,
                'scheduled_at' => $nextSlot->slot_date->format('Y-m-d').' '.$nextSlot->start_time,
                'status' => 'scheduled',
                'visit_mode' => $appointment->visit_mode,
                'notes' => $appointment->notes,
                'rescheduled_from_id' => $appointment->id,
            ]);
        });

        return response()->json([
            'message' => 'Appointment rescheduled.',
            'appointment' => $rescheduled,
        ]);
    }

    public function createTelemedicineSession(Request $request, Appointment $appointment): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        if ($appointment->tenant_id !== null && (int) $appointment->tenant_id !== (int) $tenantId) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        $meetingId = 'tm-'.now()->format('YmdHis').'-'.$appointment->id;
        $url = 'https://meet.example.com/'.$meetingId;

        $session = TelemedicineSession::create([
            'tenant_id' => $tenantId,
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
