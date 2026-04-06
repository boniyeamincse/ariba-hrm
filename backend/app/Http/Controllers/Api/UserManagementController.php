<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;

        $users = User::query()
            ->where('tenant_id', $tenantId)
            ->with('roles:id,name')
            ->paginate(20);

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'string', 'max:100'],
        ]);

        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;

        $user = User::query()->create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        if (! empty($data['role'])) {
            $role = Role::query()->firstOrCreate(['name' => $data['role']]);
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        return response()->json($user->load('roles:id,name'), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;

        if ((int) $user->tenant_id !== (int) $tenantId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
        ]);

        if (array_key_exists('role', $data) && ! empty($data['role'])) {
            $role = Role::query()->firstOrCreate(['name' => $data['role']]);
            $user->roles()->sync([$role->id]);
        }

        return response()->json($user->load('roles:id,name'));
    }
}
