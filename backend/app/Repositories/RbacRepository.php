<?php

namespace App\Repositories;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\RbacAuditLog;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RbacRepository
{
    private ?Tenant $tenant;

    public function __construct(?Tenant $tenant = null)
    {
        $this->tenant = $tenant;
    }

    // Roles
    public function getRoles(array $filters = []): Collection
    {
        $query = Role::query();

        if ($this->tenant) {
            $query->where('tenant_id', $this->tenant->id);
        }

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (isset($filters['is_system'])) {
            $query->where('is_system', $filters['is_system']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('display_name', 'like', "%{$filters['search']}%");
            });
        }

        return $query->with(['permissions', 'creator', 'updater'])->orderBy('sort_order', 'asc')->orderBy('name')->get();
    }

    public function getRoleById(int $roleId): ?Role
    {
        return $this->getRoles()
            ->where('id', $roleId)
            ->first();
    }

    public function createRole(array $data): Role
    {
        $data['tenant_id'] = $this->tenant?->id;
        $data['created_by'] = auth()->id();

        return Role::create($data);
    }

    public function updateRole(Role $role, array $data): Role
    {
        $data['updated_by'] = auth()->id();
        $role->update($data);

        return $role->refresh();
    }

    public function deleteRole(Role $role): bool
    {
        if ($role->is_system) {
            throw new \Exception('Cannot delete system role');
        }

        return $role->delete();
    }

    // Permissions
    public function getPermissions(array $filters = []): Collection
    {
        $query = Permission::query();

        if ($this->tenant) {
            $query->where('tenant_id', $this->tenant->id);
        }

        if (isset($filters['is_system'])) {
            $query->where('is_system', $filters['is_system']);
        }

        if (isset($filters['module_key'])) {
            $query->where('module_key', $filters['module_key']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('display_name', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('module_key')->orderBy('name')->get();
    }

    public function getPermissionById(int $permissionId): ?Permission
    {
        return $this->getPermissions()
            ->where('id', $permissionId)
            ->first();
    }

    public function createPermission(array $data): Permission
    {
        $data['tenant_id'] = $this->tenant?->id;

        return Permission::create($data);
    }

    public function updatePermission(Permission $permission, array $data): Permission
    {
        if ($permission->is_system) {
            throw new \Exception('Cannot update system permission');
        }

        $permission->update($data);

        return $permission->refresh();
    }

    public function deletePermission(Permission $permission): bool
    {
        if ($permission->is_system) {
            throw new \Exception('Cannot delete system permission');
        }

        return $permission->delete();
    }

    // Permission Groups
    public function getPermissionGroups(): Collection
    {
        $query = PermissionGroup::query();

        if ($this->tenant) {
            $query->where('tenant_id', $this->tenant->id);
        }

        return $query->active()->orderBy('sort_order')->orderBy('name')->get();
    }

    public function getPermissionGroupById(int $groupId): ?PermissionGroup
    {
        return $this->getPermissionGroups()
            ->where('id', $groupId)
            ->first();
    }

    public function createPermissionGroup(array $data): PermissionGroup
    {
        $data['tenant_id'] = $this->tenant?->id;

        return PermissionGroup::create($data);
    }

    public function updatePermissionGroup(PermissionGroup $group, array $data): PermissionGroup
    {
        $group->update($data);

        return $group->refresh();
    }

    // Role-Permission Relationships
    public function syncRolePermissions(Role $role, array $permissionIds): Role
    {
        $permissions = Permission::whereIn('id', $permissionIds)->get();

        if ($this->tenant) {
            $permissions = $permissions->where('tenant_id', $this->tenant->id);
        }

        $role->syncPermissions($permissions);

        return $role;
    }

    public function grantPermissionToRole(Role $role, Permission $permission): void
    {
        if ($this->tenant && ($role->tenant_id !== $this->tenant->id || $permission->tenant_id !== $this->tenant->id)) {
            throw new \Exception('Tenant boundary violation');
        }

        $role->givePermissionTo($permission);
    }

    public function revokePermissionFromRole(Role $role, Permission $permission): void
    {
        $role->revokePermissionTo($permission);
    }

    // User-Role Relationships
    public function assignRoleToUser(User $user, Role $role): void
    {
        if ($this->tenant && $role->tenant_id !== $this->tenant->id) {
            throw new \Exception('Tenant boundary violation');
        }

        $user->assignRole($role);
    }

    public function removeRoleFromUser(User $user, Role $role): void
    {
        $user->removeRole($role);
    }

    public function syncUserRoles(User $user, array $roleIds): User
    {
        $roles = Role::whereIn('id', $roleIds)->get();

        if ($this->tenant) {
            $roles = $roles->where('tenant_id', $this->tenant->id);
        }

        $user->syncRoles($roles);

        return $user;
    }

    public function getUserRoles(User $user): Collection
    {
        $roles = $user->roles();

        if ($this->tenant) {
            $roles->where('tenant_id', $this->tenant->id);
        }

        return $roles->get();
    }

    // RBAC Audit Log
    public function logAudit(string $action, string $targetType, int $targetId, ?array $oldValues = null, ?array $newValues = null): void
    {
        RbacAuditLog::create([
            'tenant_id' => $this->tenant?->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
