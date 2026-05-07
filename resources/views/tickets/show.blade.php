@extends('layouts.erp')

@php
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.route('tickets.index').'"><i class="bi bi-arrow-left me-1"></i>Back</a> <a class="btn btn-sm btn-primary" href="'.route('tickets.edit', $ticket).'"><i class="bi bi-pencil me-1"></i>Edit</a>';
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
    @endif

    <div class="erp-ticket-layout">
        <section class="erp-panel">
            <div class="erp-panel-header">
                <div>
                    <h2 class="erp-panel-title">{{ $ticket->ticket_no }} - {{ $ticket->subject }}</h2>
                    <div class="text-muted mt-1">{{ $ticket->created_at?->format('Y-m-d H:i') }} by {{ $ticket->requester?->name }}</div>
                </div>
                <div class="d-flex gap-1">
                    @include('tickets.partials.badge', ['type' => 'status', 'value' => $ticket->status])
                    @include('tickets.partials.badge', ['type' => 'priority', 'value' => $ticket->priority])
                </div>
            </div>
            <div class="erp-panel-body">
                <article class="erp-ticket-message">
                    <div class="erp-ticket-avatar">{{ strtoupper(substr($ticket->requester?->name ?? 'U', 0, 1)) }}</div>
                    <div class="erp-ticket-bubble">
                        <div class="fw-semibold">{{ $ticket->requester?->name ?? '-' }}</div>
                        <p class="mb-0">{{ $ticket->description }}</p>
                    </div>
                </article>

                @foreach ($ticket->comments as $comment)
                    <article class="erp-ticket-message">
                        <div class="erp-ticket-avatar">{{ strtoupper(substr($comment->user?->name ?? 'U', 0, 1)) }}</div>
                        <div class="erp-ticket-bubble">
                            <div class="d-flex justify-content-between gap-2">
                                <strong>{{ $comment->user?->name ?? '-' }}</strong>
                                <span class="text-muted">{{ $comment->created_at?->format('Y-m-d H:i') }}</span>
                            </div>
                            <p class="mb-2">{{ $comment->comment }}</p>
                            @foreach ($comment->attachments as $attachment)
                                <a class="erp-attachment" href="{{ $attachment->url }}" target="_blank"><i class="bi bi-paperclip"></i>{{ $attachment->original_name }}</a>
                            @endforeach
                        </div>
                    </article>
                @endforeach

                <form class="mt-3" method="POST" action="{{ route('tickets.comment', $ticket) }}" enctype="multipart/form-data">
                    @csrf
                    <label class="form-label">Comment</label>
                    <textarea class="form-control @error('comment') is-invalid @enderror" name="comment" rows="4"></textarea>
                    @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-2">
                        <input class="form-control" type="file" name="attachments[]" multiple>
                        <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-send me-1"></i>Send</button>
                    </div>
                </form>
            </div>
        </section>

        <aside class="d-grid gap-2">
            <section class="erp-panel">
                <div class="erp-panel-header"><h2 class="erp-panel-title">Ticket Information</h2></div>
                <div class="erp-panel-body erp-info-list">
                    <div><span>Category</span><strong>{{ $ticket->category?->name ?? '-' }}</strong></div>
                    <div><span>Requester</span><strong>{{ $ticket->requester?->name ?? '-' }}</strong></div>
                    <div><span>Assigned To</span><strong>{{ $ticket->assignee?->name ?? '-' }}</strong></div>
                    <div><span>Jabatan</span><strong>{{ $ticket->jabatan?->name ?? '-' }}</strong></div>
                    <div><span>Source</span><strong>{{ $ticket->source }}</strong></div>
                    <div><span>Response Due</span><strong>{{ $ticket->response_due_at?->format('Y-m-d H:i') ?? '-' }}</strong></div>
                    <div><span>Resolve Due</span><strong>{{ $ticket->resolve_due_at?->format('Y-m-d H:i') ?? '-' }}</strong></div>
                    <div><span>SLA</span><strong>{{ $ticket->sla?->name ?? '-' }}</strong></div>
                </div>
            </section>

            <section class="erp-panel">
                <div class="erp-panel-header"><h2 class="erp-panel-title">Actions</h2></div>
                <div class="erp-panel-body">
                    <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="mb-2">
                        @csrf
                        <label class="form-label">Assign To</label>
                        <select class="form-select js-select2 mb-2" name="assigned_to">
                            @foreach ($users as $user)<option value="{{ $user->id }}" @selected($ticket->assigned_to === $user->id)>{{ $user->name }}</option>@endforeach
                        </select>
                        <input class="form-control mb-2" name="note" placeholder="Assignment note">
                        <button class="btn btn-sm btn-outline-primary w-100" type="submit"><i class="bi bi-person-check me-1"></i>Assign</button>
                    </form>

                    <form method="POST" action="{{ route('tickets.status', $ticket) }}">
                        @csrf
                        <label class="form-label">Change Status</label>
                        <select class="form-select js-select2 mb-2" name="status">
                            @foreach (\App\Models\Ticket::STATUSES as $status)<option value="{{ $status }}" @selected($ticket->status === $status)>{{ str_replace('_', ' ', $status) }}</option>@endforeach
                        </select>
                        <input class="form-control mb-2" name="note" placeholder="Status note">
                        <button class="btn btn-sm btn-outline-primary w-100" type="submit"><i class="bi bi-arrow-repeat me-1"></i>Update Status</button>
                    </form>
                </div>
            </section>

            <section class="erp-panel">
                <div class="erp-panel-header"><h2 class="erp-panel-title">Attachments</h2></div>
                <div class="erp-panel-body">
                    @forelse ($ticket->attachments as $attachment)
                        <a class="erp-attachment" href="{{ $attachment->url }}" target="_blank"><i class="bi bi-paperclip"></i>{{ $attachment->original_name }}</a>
                    @empty
                        <div class="text-muted">No attachments.</div>
                    @endforelse
                </div>
            </section>

            <section class="erp-panel">
                <div class="erp-panel-header"><h2 class="erp-panel-title">Timeline Activity</h2></div>
                <div class="erp-panel-body erp-timeline">
                    @foreach ($ticket->logs->sortByDesc('created_at') as $log)
                        <div class="erp-timeline-item">
                            <strong>{{ str_replace('_', ' ', $log->action) }}</strong>
                            <span>{{ $log->user?->name ?? 'System' }} - {{ $log->created_at?->format('Y-m-d H:i') }}</span>
                            @if ($log->note)<p>{{ $log->note }}</p>@endif
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
@endsection
