<?php

namespace App\Http\Controllers\Api\V1\Rbac;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Rbac\AssignRoleToUserRequest;
use App\Http\Requests\Api\V1\Rbac\CreatePermissionGroupRequest;
use App\Http\Requests\Api\V1\Rbac\CreatePermissionRequest;
use App\Http\Requests\Api\V1\Rbac\CreateRoleRequest;
use App\Http\Requests\Api\V1\Rbac\DeleteRoleRequest;
use App\Http\Requests\Api\V1\Rbac\SyncPermissionsRequest;
use App\Http\Requests\Api\V1\Rbac\UpdatePermissionRequest;
use App\Http\Requests\Api\V1\Rbac\UpdateRoleRequest;
use App\Http\Resources\Api\V1\Rbac\PermissionGroupResource;
use App\Http\Resources\Api\V1\Rbac\PermissionResource;
use App\Http\Resources\Api\V1\Rbac\RoleResource;
use App\Http\Resources\Api\V1\Rbac\RoleWithPermissionsResource;
use App\Models\User;
use App\Services\RbacService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RbacController extends Controller
{
    private RbacService $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    // ===================== ROLES =====================

    /**
     * List all roles with filters
     * GET /api/v1/rbac/roles
     */
    public function indexRoles(): ResourceCollection
    {
        $this->authorize('rbac:view_roles');

        $filters = [];
        if (request()->has('search')) {
            $filters['search'] = request()->get('search');
        }
        if (request()->has('active')) {
            $filters['active'] = filter_var(request()->get('active'), FILTER_VALIDATE_BOOLEAN);
        }

        $roles = $this->rbacService->listRoles($filters);

        return RoleResource::collection($roles);
    }

    /**
     * Get role with permissions
     * GET /api/v1/rbac/roles/{id}
     */
    public function showRole(int $id): RoleWithPermissionsResource|JsonResponse
    {
        $this->authorize('rbac:view_roles');

        $role = $this->rbacService->getRole($id);
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return new RoleWithPermissionsResource($role);
    }

    /**
     * Create new role
     * POST /api/v1/rbac/roles
     */
    public function storeRole(CreateRoleRequest $request): RoleWithPermissionsResource|JsonResponse
    {
        try {
            $role = $this->rbacService->createRole($request->validated());

            if ($request->filled('permission_ids')) {
                $this->rbacService->syncRolePermissions($role->id, $request->permission_ids);
                $role = $this->rbacService->getRole($role->id);
            }

            return new RoleWithPermissionsResource($role);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update role
     * PATCH /api/v1/rbac/roles/{id}
     */
    public function updateRole(int $id, UpdateRoleRequest $request): RoleWithPermissionsResource|JsonResponse
    {
        try {
            $role = $this->rbacService->updateRole($id, $request->validated());

            if ($request->filled('permission_ids')) {
                $this->rbacService->syncRolePermissions($role->id, $request->permission_ids);
                $role = $this->rbacService->getRole($role->id);
            }

            return new RoleWithPermissionsResource($role);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete role
     * DELETE /api/v1/rbac/roles/{id}
     */
    public function deleteRole(int $id, DeleteRoleRequest $request): JsonResponse
    {
        try {
            $this->rbacService->deleteRole($id);

            return response()->json(['message' => 'Role deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ===================== PERMISSIONS =====================

    /**
     * List all permissions with filters
     * GET /api/v1/rbac/permissions
     */
    public function indexPermissions(): ResourceCollection
    {
        $this->authorize('rbac:view_permissions');

        $filters = [];
        if (request()->has('module_key')) {
            $filters['module_key'] = request()->get('module_key');
        }
        if (request()->has('search')) {
            $filters['search'] = request()->get('search');
        }

        $permissions = $this->rbacService->listPermissions($filters);

        return PermissionResource::collection($permissions);
    }

    /**
     * Get permission
     * GET /api/v1/rbac/permissions/{id}
     */
    public function showPermission(int $id): PermissionResource|JsonResponse
    {
        $this->authorize('rbac:view_permissions');

        $permission = $this->rbacService->getPermission($id);
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        return new PermissionResource($permission);
    }

    /**
     * Create permission
     * POST /api/v1/rbac/permissions
     */
    public function storePermission(CreatePermissionRequest $request): PermissionResource|JsonResponse
    {
        try {
            $permission = $this->rbacService->createPermission($request->validated());

            return new PermissionResource($permission);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update permission
     * PATCH /api/v1/rbac/permissions/{id}
     */
    public function updatePermission(int $id, UpdatePermissionRequest $request): PermissionResource|JsonResponse
    {
        try {
            $permission = $this->rbacService->updatePermission($id, $request->validated());

            return new PermissionResource($permission);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete permission
     * DELETE /api/v1/rbac/permissions/{id}
     */
    public function deletePermission(int $id): JsonResponse
    {
        $this->authorize('rbac:delete_permission');

        try {
            $this->rbacService->deletePermission($id);

            return response()->json(['message' => 'Permission deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ===================== ROLE-PERMISSION MAPPING =====================

    /**
     * Sync role permissions
     * PUT /api/v1/rbac/roles/{id}/permissions
     */
    public function syncRolePermissions(int $id, SyncPermissionsRequest $request): RoleWithPermissionsResource|JsonResponse
    {
        try {
            $role = $this->rbacService->syncRolePermissions($id, $request->permission_ids);

            return new RoleWithPermissionsResource($role);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ===================== USER-ROLE ASSIGNMENT =====================

    /**
     * Assign roles to user
     * POST /api/v1/rbac/users/{userId}/roles
     */
    public function assignRolesToUser(int $userId, AssignRoleToUserRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($userId);

            $this->rbacService->syncUserRoles($userId, $request->role_ids);

            $user = $user->fresh()->load('roles.permissions');

            return response()->json([
                'message' => 'Roles assigned successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => RoleResource::collection($user->roles),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove role from user
     * DELETE /api/v1/rbac/users/{userId}/roles/{roleId}
     */
    public function removeRoleFromUser(int $userId, int $roleId): JsonResponse
    {
        try {
            $this->authorize('rbac:assign_role');

            $this->rbacService->removeRoleFromUser($userId, $roleId);

            $user = User::find($userId)->fresh()->load('roles');

            return response()->json([
                'message' => 'Role removed successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'roles' => RoleResource::collection($user->roles),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ===================== PERMISSION GROUPS =====================

    /**
     * List permission groups
     * GET /api/v1/rbac/permission-groups
     */
    public function indexPermissionGroups(): ResourceCollection
    {
        $groups = $this->rbacService->listPermissionGroups();

        return PermissionGroupResource::collection($groups);
    }

    /**
     * Create permission group
     * POST /api/v1/rbac/permission-groups
     */
    public function storePermissionGroup(CreatePermissionGroupRequest $request): PermissionGroupResource|JsonResponse
    {
        try {
            $this->authorize('rbac:manage_groups');

            $group = $this->rbacService->createPermissionGroup($request->validated());

            return new PermissionGroupResource($group);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ===================== RBAC MATRIX (DASHBOARD) =====================

    /**
     * Get full RBAC permission matrix
     * GET /api/v1/rbac/matrix
     */
    public function getMatrix(): JsonResponse
    {
        $this->authorize('rbac:view_matrix');

        try {
            $matrix = $this->rbacService->getPermissionMatrix();

            return response()->json([
                'matrix' => $matrix,
                'total_roles' => count($matrix[array_key_first($matrix)]['roles'] ?? []),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
