<?php

namespace App\Models;

use App\Models\Master\RefJabatan;
use App\Models\Master\RefTicketCategory;
use App\Models\Master\RefTicketSla;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    public const STATUSES = ['OPEN', 'ASSIGNED', 'IN_PROGRESS', 'PENDING', 'RESOLVED', 'CLOSED', 'REJECTED'];

    public const PRIORITIES = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];

    public const SOURCES = ['WEB', 'EMAIL', 'WHATSAPP', 'API', 'MOBILE'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'response_due_at' => 'datetime',
            'resolve_due_at' => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'is_overdue' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RefTicketCategory::class, 'category_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(RefJabatan::class, 'jabatan_id');
    }

    public function sla(): BelongsTo
    {
        return $this->belongsTo(RefTicketSla::class, 'sla_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TicketAssignment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TicketLog::class);
    }
}
