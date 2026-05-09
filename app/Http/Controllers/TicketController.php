<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketAssignmentRequest;
use App\Http\Requests\TicketCommentRequest;
use App\Http\Requests\TicketRequest;
use App\Http\Requests\TicketStatusRequest;
use App\Models\Master\RefJabatan;
use App\Models\Master\RefTicketCategory;
use App\Models\Master\SysUser;
use App\Models\Ticket;
use App\Services\TicketAccessService;
use App\Services\TicketService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $tickets,
        private readonly TicketAccessService $ticketAccess,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Ticket::class);

        $user = auth()->user();
        $ticketTabs = $this->ticketAccess->tabsFor($user);
        $tabCounts = $this->ticketAccess->countsFor($user);

        return view('tickets.index', $this->viewData([
            'categories' => RefTicketCategory::orderBy('name')->get(['id', 'name']),
            'users' => SysUser::orderBy('name')->get(['id', 'name']),
            'jabatan' => RefJabatan::orderBy('name')->get(['id', 'name']),
            'summary' => $this->summary(),
            'ticketTabs' => $ticketTabs,
            'tabCounts' => $tabCounts,
            'defaultTicketScope' => $this->ticketAccess->defaultTab($ticketTabs),
        ]));
    }

    public function datatable(Request $request)
    {
        Gate::authorize('viewAny', Ticket::class);

        $query = Ticket::query()->with(['category', 'requester', 'assignee', 'jabatan']);

        $this->applyScope($query, $request);
        $recordsTotal = (clone $query)->count();

        $this->applyFilters($query, $request);
        $this->applySearch($query, $request->input('search.value') ?: $request->input('keyword'));

        $recordsFiltered = (clone $query)->count();
        $this->applyOrdering($query, $request);

        $start = max((int) $request->input('start', 0), 0);
        $length = in_array((int) $request->input('length', 10), [10, 25, 50, 100], true) ? (int) $request->input('length', 10) : 10;

        $rows = $query->skip($start)->take($length)->get()->map(function (Ticket $ticket, int $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1,
                'ticket_no' => '<a href="'.route('tickets.show', $ticket).'">'.e($ticket->ticket_no).'</a>',
                'subject' => e($ticket->subject),
                'category' => e($ticket->category?->name ?? '-'),
                'priority' => view('tickets.partials.badge', ['type' => 'priority', 'value' => $ticket->priority])->render(),
                'status' => view('tickets.partials.badge', ['type' => 'status', 'value' => $ticket->status])->render(),
                'requester' => e($ticket->requester?->name ?? '-'),
                'assignee' => e($ticket->assignee?->name ?? '-'),
                'created_at' => e($ticket->created_at?->format('Y-m-d H:i') ?? '-'),
                'actions' => View::make('tickets.partials.actions', ['ticket' => $ticket])->render(),
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
            'tabCounts' => $this->ticketAccess->countsFor(auth()->user()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Ticket::class);

        return view('tickets.form', $this->viewData($this->formOptions([
            'ticket' => new Ticket(['priority' => 'MEDIUM']),
            'mode' => 'create',
        ])));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketRequest $request)
    {
        Gate::authorize('create', Ticket::class);

        $ticket = $this->tickets->create($request->validated(), $request->file('attachments', []), $request);

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $ticket->load([
            'category', 'requester', 'assignee', 'resolvedBy', 'closedBy', 'jabatan', 'sla',
            'attachments.uploader',
            'comments.user', 'comments.attachments.uploader',
            'assignments.fromUser', 'assignments.toUser',
            'statusHistories.changedBy',
            'logs.user',
        ]);

        return view('tickets.show', $this->viewData($this->formOptions([
            'ticket' => $ticket,
        ])));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        return view('tickets.form', $this->viewData($this->formOptions([
            'ticket' => $ticket,
            'mode' => 'edit',
        ])));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TicketRequest $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        $this->tickets->update($ticket, $request->validated(), $request->file('attachments', []), $request);

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        Gate::authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')->with('status', 'Ticket berhasil dihapus.');
    }

    public function comment(TicketCommentRequest $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $this->tickets->comment($ticket, $request->validated(), $request->file('attachments', []), $request);

        return back()->with('status', 'Comment berhasil ditambahkan.');
    }

    public function assign(TicketAssignmentRequest $request, Ticket $ticket)
    {
        Gate::authorize('assign', $ticket);

        $this->tickets->assign($ticket, $request->validated('assigned_to'), $request, $request->validated('note'));

        return back()->with('status', 'Ticket berhasil di-assign.');
    }

    public function changeStatus(TicketStatusRequest $request, Ticket $ticket)
    {
        Gate::authorize('changeStatus', $ticket);

        $this->tickets->changeStatus($ticket, $request->validated('status'), $request, $request->validated('note'));

        return back()->with('status', 'Status ticket berhasil diperbarui.');
    }

    private function viewData(array $data = []): array
    {
        return array_merge([
            'title' => 'Tickets',
            'subtitle' => 'Transaction ticketing workflow',
            'breadcrumbs' => [
                ['label' => 'Desk', 'url' => route('home')],
                ['label' => 'Transaction'],
                ['label' => 'Tickets'],
            ],
        ], $data);
    }

    private function formOptions(array $data = []): array
    {
        return array_merge([
            'categories' => RefTicketCategory::orderBy('name')->get(['id', 'name']),
            'users' => $this->assignableUsers(),
            'jabatan' => RefJabatan::orderBy('name')->get(['id', 'name']),
        ], $data);
    }

    private function assignableUsers()
    {
        return SysUser::query()
            ->whereHas('roles', fn (Builder $role) => $role->where('code', 'PICTICKET'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        foreach (['status', 'priority', 'category_id', 'requester_id', 'assigned_to', 'jabatan_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
    }

    private function applyScope(Builder $query, Request $request): void
    {
        $scope = $this->ticketAccess->resolveRequestedTab(auth()->user(), $request->input('ticket_scope'));
        $this->ticketAccess->applyTabScope($query, $scope, auth()->user());
    }

    private function applySearch(Builder $query, ?string $keyword): void
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $query->where(function (Builder $inner) use ($keyword) {
            $inner->where('ticket_no', 'like', '%'.$keyword.'%')
                ->orWhere('subject', 'like', '%'.$keyword.'%')
                ->orWhere('status', 'like', '%'.$keyword.'%')
                ->orWhere('priority', 'like', '%'.$keyword.'%')
                ->orWhereHas('requester', fn (Builder $user) => $user->where('name', 'like', '%'.$keyword.'%'))
                ->orWhereHas('assignee', fn (Builder $user) => $user->where('name', 'like', '%'.$keyword.'%'));
        });
    }

    private function applyOrdering(Builder $query, Request $request): void
    {
        $columns = ['ticket_no', 'subject', 'category_id', 'priority', 'status', 'requester_id', 'assigned_to', 'created_at'];
        $order = collect($request->input('order', []))->first();
        $index = max(((int) ($order['column'] ?? 1)) - 1, 0);
        $direction = strtolower($order['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($columns[$index] ?? 'created_at', $direction);
    }

    private function summary(): array
    {
        $base = fn () => $this->ticketAccess->applyVisibleScope(Ticket::query(), auth()->user());

        return [
            'Open Ticket' => $base()->where('status', 'OPEN')->count(),
            'Assigned Ticket' => $base()->where('status', 'ASSIGNED')->count(),
            'Overdue Ticket' => $base()->where('is_overdue', true)->count(),
            'Resolved Ticket' => $base()->where('status', 'RESOLVED')->count(),
            'Closed Ticket' => $base()->where('status', 'CLOSED')->count(),
            'Critical Ticket' => $base()->where('priority', 'CRITICAL')->count(),
        ];
    }
}
