<?php

namespace App\Traits;

use Spatie\Permission\Models\Role;

trait RoleAssignmentTrait
{
    public function assignRoleWithPrefix($role, $guard = null)
    {
        $guard = $guard ?: $this->guard_name;

        $roles = is_array($role) ? $role : [$role];
        
        foreach ($roles as $roleName) {
            $roleModel = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard
            ]);
            
            // استخدام ID العادي بدون بادئة في الإدراج
            $this->roles()->attach($roleModel, [
                'model_type' => get_class($this),
                'model_id' => $this->getAuthIdentifier() // بدون بادئة هنا
            ]);
        }
    }
}