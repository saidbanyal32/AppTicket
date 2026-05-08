<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends BaseMasterModel
{
    protected $table = 'sys_role_permissions';

    public function role(): BelongsTo
    {
        return $this->belongsTo(SysRole::class, 'role_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(SysPermission::class, 'permission_id');
    }
}
