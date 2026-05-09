<?php

namespace App\Models\Master;

use App\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as SpatiePermission;

class SysPermission extends SpatiePermission
{
    use HasAuditFields, SoftDeletes;

    public const TYPE_GLOBAL_ACTION = 'global_action';

    protected $table = 'sys_permissions';

    protected $guarded = ['id'];

    protected $guard_name = 'web';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(SysRole::class, 'sys_role_permissions', 'permission_id', 'role_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(SysModule::class, 'module_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(SysAction::class, 'action_id');
    }
}
