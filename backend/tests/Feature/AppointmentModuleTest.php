<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Tests\TestCase;

class AppointmentModuleTest extends TestCase
{
    private string $host = 'alpha.medcore.test';

    private Tenant $tenant;

    private string $token;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('The MySQL PDO driver is not available in this environment.');
        }

        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->seed(RolePermissionSeeder::class);

        $this->tenant = Tenant::query()->create([
            'name' => 'Alpha Hospital',
            'subdomain' => 'alpha',
            'database_name' => 'alpha_hospital',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $hospitalAdminRole = Role::query()->where('name', 'hospital-admin')->firstOrFail();
        $user->roles()->syncWithoutDetaching([$hospitalAdminRole->id]);

        $this->token = $user->createToken('appointment-tests')->plainTextToken;
    }

    public function test_can_book_appointment(): void
    {
        $patient = Patient::query()->create([
            'tenant_id' => $this->tenant->id,
            'uhid' => 'HMS-'.now()->format('Y').'-000901',
            'first_name' => 'Rafi',
            'date_of_birth' => '1995-02-11',
            'phone' => '01755550000',
        ]);

        $slot = AppointmentSlot::query()->create([
            'tenant_id' => null,
            'doctor_id' => null,
            'slot_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '10:30',
            'max_patients' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        $response = $this->tenantRequest('post', '/api/clinical/opd/appointments/book', [
            'patient_id' => $patient->id,
            'appointment_slot_id' => $slot->id,
            'visit_mode' => 'in_person',
            'notes' => 'First visit',
        ]);

        $response->assertCreated()
            ->assertJsonPath('appointment.patient_id', $patient->id)
            ->assertJsonPath('appointment.status', 'scheduled');

        $slot->refresh();
        $this->assertSame(1, $slot->booked_count);
    }

    public function test_can_reschedule_appointment(): void
    {
        $patient = Patient::query()->create([
            'tenant_id' => $this->tenant->id,
            'uhid' => 'HMS-'.now()->format('Y').'-000902',
            'first_name' => 'Maya',
            'date_of_birth' => '1994-09-12',
            'phone' => '01755550001',
        ]);

        $firstSlot = AppointmentSlot::query()->create([
            'tenant_id' => null,
            'doctor_id' => null,
            'slot_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '09:30',
            'max_patients' => 2,
            'booked_count' => 1,
            'is_active' => true,
        ]);

        $secondSlot = AppointmentSlot::query()->create([
            'tenant_id' => null,
            'doctor_id' => null,
            'slot_date' => now()->addDays(2)->toDateString(),
            'start_time' => '11:00',
            'end_time' => '11:30',
            'max_patients' => 2,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        $appointment = Appointment::query()->create([
            'tenant_id' => null,
            'patient_id' => $patient->id,
            'doctor_id' => null,
            'appointment_slot_id' => $firstSlot->id,
            'scheduled_at' => now()->addDay()->setTime(9, 0),
            'status' => 'scheduled',
            'visit_mode' => 'in_person',
            'notes' => null,
        ]);

        $response = $this->tenantRequest('post', '/api/clinical/opd/appointments/'.$appointment->id.'/reschedule', [
            'appointment_slot_id' => $secondSlot->id,
            'reason' => 'Doctor requested time change',
        ]);

        $response->assertOk()
            ->assertJsonPath('appointment.patient_id', $patient->id)
            ->assertJsonPath('appointment.status', 'scheduled')
            ->assertJsonPath('appointment.rescheduled_from_id', $appointment->id);

        $appointment->refresh();
        $firstSlot->refresh();
        $secondSlot->refresh();

        $this->assertSame('rescheduled', $appointment->status);
        $this->assertSame(0, $firstSlot->booked_count);
        $this->assertSame(1, $secondSlot->booked_count);
    }

    public function test_can_cancel_appointment(): void
    {
        $patient = Patient::query()->create([
            'tenant_id' => $this->tenant->id,
            'uhid' => 'HMS-'.now()->format('Y').'-000903',
            'first_name' => 'Nafiz',
            'date_of_birth' => '1991-01-01',
            'phone' => '01755550002',
        ]);

        $slot = AppointmentSlot::query()->create([
            'tenant_id' => null,
            'doctor_id' => null,
            'slot_date' => now()->addDay()->toDateString(),
            'start_time' => '12:00',
            'end_time' => '12:30',
            'max_patients' => 2,
            'booked_count' => 1,
            'is_active' => true,
        ]);

        $appointment = Appointment::query()->create([
            'tenant_id' => null,
            'patient_id' => $patient->id,
            'doctor_id' => null,
            'appointment_slot_id' => $slot->id,
            'scheduled_at' => now()->addDay()->setTime(12, 0),
            'status' => 'scheduled',
            'visit_mode' => 'in_person',
            'notes' => null,
        ]);

        $response = $this->tenantRequest('post', '/api/clinical/opd/appointments/'.$appointment->id.'/cancel', [
            'reason' => 'Patient unavailable',
        ]);

        $response->assertOk()
            ->assertJsonPath('appointment.status', 'cancelled')
            ->assertJsonPath('appointment.cancel_reason', 'Patient unavailable');

        $slot->refresh();
        $this->assertSame(0, $slot->booked_count);
    }

    private function tenantRequest(string $method, string $uri, array $data = [])
    {
        $request = $this->withServerVariables(['HTTP_HOST' => $this->host])
            ->withHeader('Host', $this->host)
            ->withToken($this->token);

        return match ($method) {
            'post' => $request->postJson($uri, $data),
            'get' => $request->getJson($uri),
            default => throw new \InvalidArgumentException('Unsupported request method.'),
        };
    }
}
