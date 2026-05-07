<?php

namespace App\Models\Master;

use App\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SysUser extends Authenticatable
{
    use HasAuditFields, SoftDeletes;

    protected $table = 'sys_users';

    protected $guarded = ['id'];

    protected $hidden = ['password'];

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
}
