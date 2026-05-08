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

    public const TYPE_FEATURE_ACCESS = 'feature_access';

    public const TYPE_WORKFLOW_ACCESS = 'workflow_tab';

    public const TYPE_ADVANCED_ACCESS = 'advanced_access';

    protected $table = 'sys_permissions';

    protected $guarded = ['id'];

    protected $guard_name = 'web';

    protected function casts(): array
    {
        return [
            'permission_type' => 'string',
        ];
    }

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
