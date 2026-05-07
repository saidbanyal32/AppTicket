<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefTicketCategory extends BaseMasterModel
{
    protected $table = 'ref_ticket_categories';

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'sort_no' => 'integer',
        ]);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function sla(): BelongsTo
    {
        return $this->belongsTo(RefTicketSla::class, 'sla_id');
    }
}
