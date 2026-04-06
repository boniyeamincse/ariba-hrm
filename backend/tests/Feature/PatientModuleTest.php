<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PatientModuleTest extends TestCase
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

        config(['scout.driver' => 'database']);

        $this->tenant = Tenant::query()->create([
            'name' => 'Alpha Hospital',
            'subdomain' => 'alpha',
            'database_name' => 'alpha_hospital',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->token = $user->createToken('patient-tests')->plainTextToken;
    }

    public function test_patient_registration_generates_tenant_uhid_and_duplicate_flag(): void
    {
        $response = $this->tenantRequest('post', '/api/clinical/patients', [
            'first_name' => 'Rahim',
            'last_name' => 'Uddin',
            'date_of_birth' => '1992-05-10',
            'phone' => '01700001111',
            'national_id_no' => '1234567890',
            'gender' => 'male',
        ]);

        $response->assertCreated()
            ->assertJsonPath('duplicate_detected', false)
            ->assertJsonPath('patient.tenant_id', $this->tenant->id)
            ->assertJsonPath('patient.uhid', 'HMS-'.now()->format('Y').'-000001');

        $duplicateResponse = $this->tenantRequest('post', '/api/clinical/patients', [
            'first_name' => 'Rahim',
            'last_name' => 'Uddin',
            'date_of_birth' => '1992-05-10',
            'phone' => '01700001111',
        ]);

        $duplicateResponse->assertCreated()
            ->assertJsonPath('duplicate_detected', true)
            ->assertJsonStructure(['duplicate_match' => ['id', 'uhid', 'name', 'phone', 'date_of_birth']])
            ->assertJsonPath('patient.uhid', 'HMS-'.now()->format('Y').'-000002');
    }

    public function test_patient_search_update_and_medical_history_workflows(): void
    {
        $patient = Patient::query()->create([
            'tenant_id' => $this->tenant->id,
            'uhid' => 'HMS-'.now()->format('Y').'-000111',
            'first_name' => 'Karim',
            'last_name' => 'Hasan',
            'date_of_birth' => '1990-01-12',
            'phone' => '01810000000',
        ]);

        $searchResponse = $this->tenantRequest('get', '/api/clinical/patients?q=Karim');

        $searchResponse->assertOk();
        $this->assertNotEmpty($searchResponse->json('data'));

        $updateResponse = $this->tenantRequest('patch', '/api/clinical/patients/'.$patient->id, [
            'city' => 'Dhaka',
            'emergency_contact_name' => 'Amina',
            'emergency_contact_phone' => '01900000000',
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('patient.city', 'Dhaka');

        $historyResponse = $this->tenantRequest('patch', '/api/clinical/patients/'.$patient->id.'/history', [
            'allergies' => 'Penicillin',
            'chronic_conditions' => 'Diabetes',
            'surgical_history' => 'Appendectomy in 2019',
        ]);

        $historyResponse->assertOk()
            ->assertJsonPath('history.allergies', 'Penicillin');

        $this->tenantRequest('get', '/api/clinical/patients/'.$patient->id.'/history')
            ->assertOk()
            ->assertJsonPath('history.chronic_conditions', 'Diabetes');
    }

    public function test_patient_visits_timeline_can_be_created_and_listed(): void
    {
        $patient = Patient::query()->create([
            'tenant_id' => $this->tenant->id,
            'uhid' => 'HMS-'.now()->format('Y').'-000211',
            'first_name' => 'Sadia',
            'date_of_birth' => '1988-11-20',
            'phone' => '01500001122',
        ]);

        $createVisit = $this->tenantRequest('post', '/api/clinical/patients/'.$patient->id.'/visits', [
            'visit_type' => 'opd',
            'reference_no' => 'OPD-1001',
            'visit_at' => now()->subDay()->toDateTimeString(),
            'summary' => 'Follow-up consultation',
            'meta' => ['department' => 'General Medicine'],
        ]);

        $createVisit->assertCreated()
            ->assertJsonPath('visit.reference_no', 'OPD-1001');

        $timeline = $this->tenantRequest('get', '/api/clinical/patients/'.$patient->id.'/visits');

        $timeline->assertOk();
        $this->assertSame('OPD-1001', $timeline->json('data.0.reference_no'));
    }

    public function test_patient_photo_upload_stores_original_and_thumbnail_on_s3(): void
    {
        Storage::fake('s3');

        $patient = Patient::query()->create([
            'tenant_id' => $this->tenant->id,
            'uhid' => 'HMS-'.now()->format('Y').'-000311',
            'first_name' => 'Nabila',
            'date_of_birth' => '1996-04-11',
            'phone' => '01300004455',
        ]);

        $response = $this->tenantRequest(
            'post',
            '/api/clinical/patients/'.$patient->id.'/photo',
            ['photo' => UploadedFile::fake()->image('patient.jpg', 700, 700)],
            true
        );

        $response->assertOk();

        $patient->refresh();

        Storage::disk('s3')->assertExists($patient->photo_path);
        Storage::disk('s3')->assertExists($patient->photo_thumb_path);
    }

    private function tenantRequest(string $method, string $uri, array $data = [], bool $isMultipart = false)
    {
        $request = $this->withServerVariables(['HTTP_HOST' => $this->host])
            ->withHeader('Host', $this->host)
            ->withToken($this->token);

        if ($method === 'get') {
            return $request->getJson($uri);
        }

        if ($isMultipart) {
            return $request->post($uri, $data, ['Accept' => 'application/json']);
        }

        return match ($method) {
            'post' => $request->postJson($uri, $data),
            'patch' => $request->patchJson($uri, $data),
            default => throw new \InvalidArgumentException('Unsupported request method.'),
        };
    }
}
