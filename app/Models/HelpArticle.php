<?php

namespace App\Models;

use App\Models\Master\SysUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpArticle extends Model
{
    use SoftDeletes;

    public const ARTICLE_TYPES = ['USER_GUIDE', 'DEVELOPER_DOCS', 'FAQ', 'TROUBLESHOOTING'];
    public const VISIBILITIES = ['PUBLIC', 'INTERNAL', 'DEVELOPER_ONLY'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(HelpCategory::class, 'category_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(HelpArticleAttachment::class, 'article_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(SysUser::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(SysUser::class, 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function tagList(): string
    {
        return collect($this->tags ?: [])->filter()->implode(', ');
    }
}
