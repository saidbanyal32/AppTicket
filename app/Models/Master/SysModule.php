<?php

namespace App\Models\Master;

use App\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysModule extends Model
{
    use HasAuditFields, HasUuids, SoftDeletes;

    protected $table = 'sys_modules';

    protected $guarded = ['id'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_no' => 'integer',
        ];
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(SysPermission::class, 'module_id');
    }
}
