<?php

namespace App\Models\Master;

use App\Models\Concerns\HasAuditFields;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class SysUser extends Authenticatable implements CanResetPassword
{
    use HasAuditFields, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $table = 'sys_users';

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token'];

    protected $guard_name = 'web';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(RefUnit::class, 'unit_id');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(RefJabatan::class, 'jabatan_id');
    }

    public function enterpriseRoles(): BelongsToMany
    {
        return $this->belongsToMany(SysRole::class, 'sys_user_roles', 'model_id', 'role_id')
            ->wherePivot('model_type', self::class);
    }

    public function canAccessSystem(): bool
    {
        return (bool) $this->is_active && ! $this->trashed();
    }
}
