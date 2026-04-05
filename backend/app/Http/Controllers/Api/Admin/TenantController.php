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

class TenantController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Tenant::query()->latest()->get(),
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
        ]);

        return DB::transaction(function () use ($data): JsonResponse {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'subdomain' => strtolower($data['subdomain']),
                'database_name' => $data['database_name'],
                'status' => 'active',
            ]);

            $this->provisionSchemaIfSupported($tenant->database_name);

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
    }

    private function provisionSchemaIfSupported(string $databaseName): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(sprintf('CREATE DATABASE IF NOT EXISTS `%s`', str_replace('`', '``', $databaseName)));
    }
}
