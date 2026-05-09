<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpCategory extends Model
{
    use SoftDeletes;

    public const TYPES = ['USER_GUIDE', 'DEVELOPER_DOCS', 'FAQ', 'TROUBLESHOOTING'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_no' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_no')->orderBy('name');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(HelpArticle::class, 'category_id');
    }
}
