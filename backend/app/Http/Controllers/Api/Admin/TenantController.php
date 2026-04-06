<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    private const ALLOWED_STATUSES = ['active', 'inactive', 'suspended', 'trial', 'provisioning', 'archived'];

    public function index(Request $request): JsonResponse
    {
        $query = Tenant::query()->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $paginator = $query->paginate((int) $request->input('per_page', 25));

        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        $tenant->loadCount('users');

        return response()->json([
            'tenant' => $tenant,
            'summary' => [
                'users_total' => $tenant->users_count,
                'admins_total' => $tenant->users()->whereHas('roles', fn ($q) => $q->where('name', 'hospital-admin'))->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'alpha_dash', 'max:63', 'unique:tenants,subdomain'],
            'database_name' => ['required', 'alpha_dash', 'max:64', 'unique:tenants,database_name'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'metadata' => ['sometimes', 'array'],
        ]);

        $response = DB::transaction(function () use ($data): JsonResponse {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'subdomain' => strtolower($data['subdomain']),
                'database_name' => $data['database_name'],
                'status' => 'active',
                'metadata' => $this->sanitizeMetadata($data['metadata'] ?? []),
            ]);

            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
            ]);

            $hospitalAdmin = Role::where('name', 'hospital-admin')->first();

            if ($hospitalAdmin) {
                $admin->roles()->syncWithoutDetaching([$hospitalAdmin->id]);
            }

            return response()->json([
                'message' => 'Tenant onboarded successfully.',
                'tenant' => $tenant,
                'admin_user' => $admin,
            ], 201);
        });

        // DDL operations can interfere with active transactions on MySQL.
        $this->provisionSchemaIfSupported($data['database_name']);

        return $response;
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'subdomain' => [
                'sometimes',
                'required',
                'alpha_dash',
                'max:63',
                Rule::unique('tenants', 'subdomain')->ignore($tenant->id),
            ],
            'database_name' => [
                'sometimes',
                'required',
                'alpha_dash',
                'max:64',
                Rule::unique('tenants', 'database_name')->ignore($tenant->id),
            ],
            'status' => ['sometimes', 'required', Rule::in(self::ALLOWED_STATUSES)],
            'metadata' => ['sometimes', 'array'],
        ]);

        if (array_key_exists('subdomain', $data)) {
            $data['subdomain'] = strtolower($data['subdomain']);
        }

        if (array_key_exists('metadata', $data)) {
            $currentMetadata = is_array($tenant->metadata) ? $tenant->metadata : [];
            $data['metadata'] = array_replace_recursive(
                $currentMetadata,
                $this->sanitizeMetadata($data['metadata'])
            );
        }

        $tenant->update($data);

        return response()->json([
            'message' => 'Tenant updated successfully.',
            'tenant' => $tenant->fresh(),
        ]);
    }

    public function updateStatus(Request $request, Tenant $tenant): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::ALLOWED_STATUSES)],
        ]);

        $tenant->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Tenant status updated successfully.',
            'tenant' => $tenant->fresh(),
        ]);
    }

    public function updateMetadata(Request $request, Tenant $tenant): JsonResponse
    {
        $data = $request->validate([
            'metadata' => ['required', 'array'],
        ]);

        $currentMetadata = is_array($tenant->metadata) ? $tenant->metadata : [];
        $incomingMetadata = $this->sanitizeMetadata($data['metadata']);

        $tenant->update([
            'metadata' => array_replace_recursive($currentMetadata, $incomingMetadata),
        ]);

        return response()->json([
            'message' => 'Tenant metadata updated successfully.',
            'tenant' => $tenant->fresh(),
        ]);
    }

    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $data = $request->validate([
            'mode' => ['nullable', Rule::in(['archive', 'delete'])],
        ]);

        $mode = $data['mode'] ?? 'archive';

        if ($mode === 'archive') {
            $tenant->update(['status' => 'archived']);

            return response()->json([
                'message' => 'Tenant archived successfully.',
                'tenant' => $tenant->fresh(),
            ]);
        }

        if ($tenant->users()->exists()) {
            return response()->json([
                'message' => 'Cannot permanently delete tenant while users still exist. Archive instead.',
            ], 422);
        }

        $tenant->delete();

        return response()->json([
            'message' => 'Tenant deleted permanently.',
        ]);
    }

    private function provisionSchemaIfSupported(string $databaseName): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        try {
            DB::statement(sprintf('CREATE DATABASE IF NOT EXISTS `%s`', str_replace('`', '``', $databaseName)));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function sanitizeMetadata(array $metadata): array
    {
        $allowedKeys = [
            'hospital',
            'contact',
            'billing',
            'admin',
            'technical',
            'notes',
        ];

        return collect($metadata)
            ->only($allowedKeys)
            ->map(function ($value) {
                if (is_array($value)) {
                    return $value;
                }

                return (string) $value;
            })
            ->all();
    }
}
