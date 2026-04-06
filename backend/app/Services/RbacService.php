<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use App\Repositories\RbacRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class RbacService
{
    private RbacRepository $repository;

    public function __construct(RbacRepository $repository)
    {
        $this->repository = $repository;
    }

    // Role Management
    public function listRoles(array $filters = []): Collection
    {
        return $this->repository->getRoles($filters);
    }

    public function getRole(int $roleId): ?Role
    {
        return $this->repository->getRoleById($roleId);
    }

    public function createRole(array $data): Role
    {
        $this->validateRoleCreation($data);

        $role = $this->repository->createRole($data);
        $this->repository->logAudit(
            'create',
            'role',
            $role->id,
            null,
            $role->toArray()
        );

        $this->invalidateCache();

        return $role;
    }

    public function updateRole(int $roleId, array $data): Role
    {
        $role = $this->getRole($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        if ($role->is_system && isset($data['name'])) {
            throw new \Exception('Cannot modify system role name');
        }

        $oldValues = $role->toArray();
        $role = $this->repository->updateRole($role, $data);

        $this->repository->logAudit(
            'update',
            'role',
            $role->id,
            $oldValues,
            $role->toArray()
        );

        $this->invalidateCache();

        return $role;
    }

    public function deleteRole(int $roleId): bool
    {
        $role = $this->getRole($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        $this->repository->logAudit(
            'delete',
            'role',
            $role->id,
            $role->toArray(),
            null
        );

        $success = $this->repository->deleteRole($role);
        $this->invalidateCache();

        return $success;
    }

    // Permission Management
    public function listPermissions(array $filters = []): Collection
    {
        return $this->repository->getPermissions($filters);
    }

    public function getPermission(int $permissionId): ?Permission
    {
        return $this->repository->getPermissionById($permissionId);
    }

    public function createPermission(array $data): Permission
    {
        $this->validatePermissionCreation($data);

        $permission = $this->repository->createPermission($data);
        $this->repository->logAudit(
            'create',
            'permission',
            $permission->id,
            null,
            $permission->toArray()
        );

        $this->invalidateCache();

        return $permission;
    }

    public function updatePermission(int $permissionId, array $data): Permission
    {
        $permission = $this->getPermission($permissionId);
        if (!$permission) {
            throw new \Exception('Permission not found');
        }

        $oldValues = $permission->toArray();
        $permission = $this->repository->updatePermission($permission, $data);

        $this->repository->logAudit(
            'update',
            'permission',
            $permission->id,
            $oldValues,
            $permission->toArray()
        );

        $this->invalidateCache();

        return $permission;
    }

    public function deletePermission(int $permissionId): bool
    {
        $permission = $this->getPermission($permissionId);
        if (!$permission) {
            throw new \Exception('Permission not found');
        }

        $this->repository->logAudit(
            'delete',
            'permission',
            $permission->id,
            $permission->toArray(),
            null
        );

        $success = $this->repository->deletePermission($permission);
        $this->invalidateCache();

        return $success;
    }

    // Role-Permission Mapping
    public function syncRolePermissions(int $roleId, array $permissionIds): Role
    {
        $role = $this->getRole($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        $oldPermissions = $role->permissions->pluck('id')->toArray();
        $role = $this->repository->syncRolePermissions($role, $permissionIds);

        $this->repository->logAudit(
            'sync_permissions',
            'role',
            $role->id,
            ['permissions' => $oldPermissions],
            ['permissions' => $permissionIds]
        );

        $this->invalidateCache();

        return $role;
    }

    public function addPermissionToRole(int $roleId, int $permissionId): void
    {
        $role = $this->getRole($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        $permission = $this->getPermission($permissionId);
        if (!$permission) {
            throw new \Exception('Permission not found');
        }

        $this->repository->grantPermissionToRole($role, $permission);
        $this->repository->logAudit(
            'grant_permission',
            'role',
            $role->id,
            null,
            ['permission_id' => $permissionId]
        );

        $this->invalidateCache();
    }

    public function removePermissionFromRole(int $roleId, int $permissionId): void
    {
        $role = $this->getRole($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        $permission = $this->getPermission($permissionId);
        if (!$permission) {
            throw new \Exception('Permission not found');
        }

        $this->repository->revokePermissionFromRole($role, $permission);
        $this->repository->logAudit(
            'revoke_permission',
            'role',
            $role->id,
            ['permission_id' => $permissionId],
            null
        );

        $this->invalidateCache();
    }

    // User-Role Assignment
    public function assignRoleToUser(int $userId, int $roleId): void
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $role = $this->getRole($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        $this->repository->assignRoleToUser($user, $role);
        $this->repository->logAudit(
            'assign_role',
            'user',
            $userId,
            null,
            ['role_id' => $roleId]
        );

        $this->invalidateCache();
    }

    public function removeRoleFromUser(int $userId, int $roleId): void
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $role = $this->getRole($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        $this->repository->removeRoleFromUser($user, $role);
        $this->repository->logAudit(
            'remove_role',
            'user',
            $userId,
            ['role_id' => $roleId],
            null
        );

        $this->invalidateCache();
    }

    public function syncUserRoles(int $userId, array $roleIds): User
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $oldRoles = $user->roles->pluck('id')->toArray();
        $user = $this->repository->syncUserRoles($user, $roleIds);

        $this->repository->logAudit(
            'sync_roles',
            'user',
            $userId,
            ['roles' => $oldRoles],
            ['roles' => $roleIds]
        );

        $this->invalidateCache();

        return $user;
    }

    // Permission Groups
    public function listPermissionGroups(): Collection
    {
        return $this->repository->getPermissionGroups();
    }

    public function getPermissionGroup(int $groupId): ?PermissionGroup
    {
        return $this->repository->getPermissionGroupById($groupId);
    }

    public function createPermissionGroup(array $data): PermissionGroup
    {
        return $this->repository->createPermissionGroup($data);
    }

    public function updatePermissionGroup(int $groupId, array $data): PermissionGroup
    {
        $group = $this->getPermissionGroup($groupId);
        if (!$group) {
            throw new \Exception('Permission group not found');
        }

        return $this->repository->updatePermissionGroup($group, $data);
    }

    // RBAC Matrix (for dashboard)
    public function getPermissionMatrix(): array
    {
        return Cache::remember('rbac:permission_matrix', 3600, function () {
            $roles = $this->listRoles();
            $permissions = $this->listPermissions(['is_system' => false]);
            $groups = $this->listPermissionGroups();

            $matrix = [];
            foreach ($groups as $group) {
                $groupPerms = $permissions->where('module_key', $group->key);
                $matrix[$group->key] = [
                    'group' => $group,
                    'permissions' => $groupPerms->values(),
                    'roles' => $roles->map(fn ($role) => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                        'has_permissions' => $groupPerms->pluck('id')->intersect($role->permissions->pluck('id'))->toArray(),
                    ]),
                ];
            }

            return $matrix;
        });
    }

    // Validation
    private function validateRoleCreation(array $data): void
    {
        if (empty($data['name'])) {
            throw new \Exception('Role name is required');
        }

        if (Role::where('name', $data['name'])->withoutGlobalScopes()->exists()) {
            throw new \Exception('Role name must be unique');
        }
    }

    private function validatePermissionCreation(array $data): void
    {
        if (empty($data['name'])) {
            throw new \Exception('Permission name is required');
        }

        if (empty($data['module_key'])) {
            throw new \Exception('Permission module_key is required');
        }

        if (Permission::where('name', $data['name'])->withoutGlobalScopes()->exists()) {
            throw new \Exception('Permission name must be unique');
        }
    }

    // Cache Management
    private function invalidateCache(): void
    {
        Cache::forget('rbac:permission_matrix');
        Cache::forget('rbac:roles');
        Cache::forget('rbac:permissions');
    }
}
