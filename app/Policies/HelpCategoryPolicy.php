<?php

namespace App\Policies;

use App\Models\HelpCategory;
use App\Models\Master\SysUser;

class HelpCategoryPolicy
{
    public function viewAny(SysUser $sysUser): bool
    {
        return $sysUser->can('help.view');
    }

    public function view(SysUser $sysUser, HelpCategory $helpCategory): bool
    {
        return $sysUser->can('help.view');
    }

    public function create(SysUser $sysUser): bool
    {
        return $sysUser->can('help.create');
    }

    public function update(SysUser $sysUser, HelpCategory $helpCategory): bool
    {
        return $sysUser->can('help.edit');
    }

    public function delete(SysUser $sysUser, HelpCategory $helpCategory): bool
    {
        return $sysUser->can('help.delete');
    }

    public function restore(SysUser $sysUser, HelpCategory $helpCategory): bool
    {
        return $sysUser->can('help.edit');
    }

    public function forceDelete(SysUser $sysUser, HelpCategory $helpCategory): bool
    {
        return false;
    }
}
