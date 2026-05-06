<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SysRole extends BaseMasterModel
{
    protected $table = 'sys_roles';

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(SysPermission::class, 'role_permissions', 'role_id', 'permission_id');
    }
}
