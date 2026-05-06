<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefProjectSite extends BaseMasterModel
{
    protected $table = 'ref_project_sites';

    public function project(): BelongsTo
    {
        return $this->belongsTo(RefProject::class, 'project_id');
    }
}
