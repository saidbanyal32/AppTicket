<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefJabatan extends BaseMasterModel
{
    protected $table = 'ref_jabatan';

    public function unit(): BelongsTo
    {
        return $this->belongsTo(RefUnit::class, 'unit_id');
    }
}
