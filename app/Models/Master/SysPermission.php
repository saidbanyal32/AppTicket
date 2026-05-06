<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SysPermission extends BaseMasterModel
{
    protected $table = 'sys_permissions';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(SysRole::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
