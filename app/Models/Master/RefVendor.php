<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefVendor extends BaseMasterModel
{
    protected $table = 'ref_vendor';

    public function type(): BelongsTo
    {
        return $this->belongsTo(RefVendorType::class, 'vendor_type_id');
    }
}
