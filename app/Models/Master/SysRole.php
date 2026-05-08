<?php

namespace App\Models\Master;

use App\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

class SysRole extends SpatieRole
{
    use HasAuditFields, SoftDeletes;

    protected $table = 'sys_roles';

    protected $guarded = ['id'];

    protected $guard_name = 'web';

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(SysPermission::class, 'sys_role_permissions', 'role_id', 'permission_id');
    }
}
