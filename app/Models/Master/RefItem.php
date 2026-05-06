<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefItem extends BaseMasterModel
{
    protected $table = 'ref_items';

    public function category(): BelongsTo
    {
        return $this->belongsTo(RefItemCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(RefItemUnit::class, 'unit_id');
    }
}
