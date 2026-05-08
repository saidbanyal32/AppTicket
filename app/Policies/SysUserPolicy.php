<?php

namespace App\Policies;

use App\Models\Master\SysUser;

class SysUserPolicy
{
    public function viewAny(SysUser $user): bool
    {
        return $user->can('users.view');
    }

    public function view(SysUser $user, SysUser $target): bool
    {
        return $user->can('users.view') || $user->is($target);
    }

    public function create(SysUser $user): bool
    {
        return $user->can('users.create');
    }

    public function update(SysUser $user, SysUser $target): bool
    {
        return $user->can('users.update') && ! $target->hasRole('Super Admin');
    }

    public function delete(SysUser $user, SysUser $target): bool
    {
        return $user->can('users.delete') && ! $user->is($target) && ! $target->hasRole('Super Admin');
    }
}
