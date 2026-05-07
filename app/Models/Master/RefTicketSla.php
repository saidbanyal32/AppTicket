<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\HasMany;

class RefTicketSla extends BaseMasterModel
{
    protected $table = 'ref_ticket_slas';

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'response_minutes' => 'integer',
            'resolve_minutes' => 'integer',
            'escalation_minutes' => 'integer',
        ]);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(RefTicketCategory::class, 'sla_id');
    }
}
