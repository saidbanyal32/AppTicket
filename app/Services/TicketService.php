<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Master\RefTicketSla;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\TicketAttachment;
use App\Models\TicketComment;
use App\Models\TicketLog;
use App\Models\TicketStatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketService
{
    public function create(array $data, array $attachments, Request $request): Ticket
    {
        return DB::transaction(function () use ($data, $attachments, $request) {
            $userId = $this->currentUserId();
            $sla = $this->resolveSla($data['category_id'] ?? null, $data['priority'] ?? null);

            $ticket = Ticket::create([
                'ticket_no' => $this->nextTicketNo(),
                'subject' => $data['subject'],
                'description' => $data['description'],
                'category_id' => $data['category_id'] ?? null,
                'priority' => $data['priority'],
                'status' => 'OPEN',
                'source' => 'WEB',
                'requester_id' => $userId,
                'assigned_to' => $data['assigned_to'] ?? null,
                'jabatan_id' => $data['jabatan_id'] ?? null,
                'sla_id' => $sla?->id,
                'response_due_at' => $sla ? now()->addMinutes($sla->response_minutes) : null,
                'resolve_due_at' => $sla ? now()->addMinutes($sla->resolve_minutes) : null,
            ]);

            $this->recordStatus($ticket, null, 'OPEN', $userId);
            $this->log($ticket, $userId, 'created', null, null, $ticket->only(['ticket_no', 'subject', 'priority']), null, $request);
            $this->storeAttachments($ticket, $attachments, $userId);

            if ($ticket->assigned_to) {
                $this->assign($ticket, $ticket->assigned_to, $request, 'Assigned during ticket creation.');
            }

            return $ticket;
        });
    }

    public function update(Ticket $ticket, array $data, array $attachments, Request $request): Ticket
    {
        return DB::transaction(function () use ($ticket, $data, $attachments, $request) {
            $old = $ticket->only(['subject', 'description', 'category_id', 'priority', 'jabatan_id', 'assigned_to']);
            $sla = $this->resolveSla($data['category_id'] ?? null, $data['priority'] ?? null);

            $ticket->fill([
                'subject' => $data['subject'],
                'description' => $data['description'],
                'category_id' => $data['category_id'] ?? null,
                'priority' => $data['priority'],
                'jabatan_id' => $data['jabatan_id'] ?? null,
                'sla_id' => $sla?->id,
            ])->save();

            $this->storeAttachments($ticket, $attachments, $this->currentUserId());
            $this->log($ticket, $this->currentUserId(), 'updated', null, $old, $ticket->only(array_keys($old)), null, $request);

            return $ticket;
        });
    }

    public function comment(Ticket $ticket, array $data, array $attachments, Request $request): TicketComment
    {
        return DB::transaction(function () use ($ticket, $data, $attachments, $request) {
            $userId = $this->currentUserId();
            $comment = $ticket->comments()->create([
                'user_id' => $userId,
                'comment' => $data['comment'],
                'is_internal' => (bool) ($data['is_internal'] ?? false),
            ]);

            if (! $ticket->first_response_at && $ticket->requester_id !== $userId) {
                $ticket->forceFill(['first_response_at' => now()])->save();
            }

            $this->storeAttachments($ticket, $attachments, $userId, $comment);
            $this->log($ticket, $userId, 'commented', 'comment', null, ['comment_id' => $comment->id], null, $request);
            $this->notifyUser($ticket->requester_id, 'ticket_comment', 'New ticket comment', $ticket->ticket_no.' has a new comment.', route('tickets.show', $ticket));

            return $comment;
        });
    }

    public function assign(Ticket $ticket, int $assignedTo, Request $request, ?string $note = null): void
    {
        DB::transaction(function () use ($ticket, $assignedTo, $request, $note) {
            $oldAssignee = $ticket->assigned_to;

            TicketAssignment::create([
                'ticket_id' => $ticket->id,
                'assigned_from' => $oldAssignee,
                'assigned_to' => $assignedTo,
                'note' => $note,
                'created_at' => now(),
            ]);

            $ticket->forceFill([
                'assigned_to' => $assignedTo,
                'status' => $ticket->status === 'OPEN' ? 'ASSIGNED' : $ticket->status,
            ])->save();

            if ($ticket->wasChanged('status')) {
                $this->recordStatus($ticket, 'OPEN', 'ASSIGNED', $this->currentUserId());
            }

            $this->log($ticket, $this->currentUserId(), 'assigned', 'assigned_to', ['user_id' => $oldAssignee], ['user_id' => $assignedTo], $note, $request);
            $this->notifyUser($assignedTo, 'ticket_assigned', 'Ticket assigned', $ticket->ticket_no.' has been assigned to you.', route('tickets.show', $ticket));
        });
    }

    public function changeStatus(Ticket $ticket, string $status, Request $request, ?string $note = null): void
    {
        DB::transaction(function () use ($ticket, $status, $request, $note) {
            $oldStatus = $ticket->status;
            $userId = $this->currentUserId();
            $attributes = ['status' => $status];

            if ($status === 'RESOLVED') {
                $attributes['resolved_by'] = $userId;
                $attributes['resolved_at'] = now();
            }

            if ($status === 'CLOSED') {
                $attributes['closed_by'] = $userId;
                $attributes['closed_at'] = now();
            }

            $ticket->forceFill($attributes)->save();
            $this->recordStatus($ticket, $oldStatus, $status, $userId);
            $this->log($ticket, $userId, 'status_changed', 'status', ['status' => $oldStatus], ['status' => $status], $note, $request);
            $this->notifyUser($ticket->requester_id, 'ticket_status', 'Ticket status updated', $ticket->ticket_no.' is now '.$status.'.', route('tickets.show', $ticket));
        });
    }

    public function storeAttachments(Ticket $ticket, array $files, int $userId, ?TicketComment $comment = null): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());
            $fileName = Str::uuid().($extension ? '.'.$extension : '');
            $path = $file->storeAs('tickets/'.$ticket->id, $fileName, 'public');

            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'comment_id' => $comment?->id,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $fileName,
                'file_extension' => $extension,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'disk' => 'public',
                'file_path' => $path,
                'uploaded_by' => $userId,
            ]);
        }
    }

    public function nextTicketNo(): string
    {
        $prefix = 'TCK-'.now()->format('Ym').'-';
        $last = Ticket::withTrashed()->where('ticket_no', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('ticket_no')->value('ticket_no');
        $next = $last ? ((int) Str::afterLast($last, '-')) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function currentUserId(): int
    {
        $authId = auth()->id();

        if ($authId && User::whereKey($authId)->exists()) {
            return $authId;
        }

        return User::query()->value('id') ?? User::query()->create([
            'name' => 'Administrator',
            'email' => 'administrator@example.test',
            'password' => bcrypt(Str::random(24)),
        ])->id;
    }

    private function resolveSla(?int $categoryId, ?string $priority): ?RefTicketSla
    {
        if (! $priority) {
            return null;
        }

        return RefTicketSla::query()
            ->where('priority', strtolower($priority))
            ->orWhere('priority', $priority)
            ->first();
    }

    private function recordStatus(Ticket $ticket, ?string $old, string $new, int $userId): void
    {
        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'old_status' => $old,
            'new_status' => $new,
            'changed_by' => $userId,
            'created_at' => Carbon::now(),
        ]);
    }

    private function log(Ticket $ticket, ?int $userId, string $action, ?string $field, mixed $old, mixed $new, ?string $note, Request $request): void
    {
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'action' => $action,
            'field_name' => $field,
            'old_value' => $old,
            'new_value' => $new,
            'note' => $note,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function notifyUser(?int $userId, string $type, string $title, string $message, ?string $url): void
    {
        if (! $userId) {
            return;
        }

        AppNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'created_at' => now(),
        ]);
    }
}
