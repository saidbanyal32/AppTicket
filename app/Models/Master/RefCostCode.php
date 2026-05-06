<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefCostCode extends BaseMasterModel
{
    protected $table = 'ref_cost_codes';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(RefProject::class, 'project_id');
    }
}
