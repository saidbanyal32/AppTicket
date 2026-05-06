<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Relations\HasMany;

class RefProject extends BaseMasterModel
{
    protected $table = 'ref_project';

    public function sites(): HasMany
    {
        return $this->hasMany(RefProjectSite::class, 'project_id');
    }
}
