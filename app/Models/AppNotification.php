<?php

namespace App\Models;

use App\Models\Master\SysUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    public $timestamps = false;

    protected $table = 'notifications';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(SysUser::class);
    }
}
