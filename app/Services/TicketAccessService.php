<?php

namespace App\Services;

use App\Models\Master\SysUser;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TicketAccessService
{
    public function tabsFor(SysUser $user): Collection
    {
        return collect([
            ['key' => 'my_request', 'label' => 'My Request'],
            ['key' => 'need_assignment', 'label' => 'Need Assignment'],
            ['key' => 'assign_to_me', 'label' => 'Assign To Me'],
            ['key' => 'overdue', 'label' => 'Overdue'],
            ['key' => 'closed', 'label' => 'Closed'],
            ['key' => 'all', 'label' => 'All Tickets'],
        ])
            ->filter(fn (array $tab) => $this->canAccessTab($user, $tab['key']))
            ->values();
    }

    public function canAccessTab(SysUser $user, string $tab): bool
    {
        if ($this->hasFullAccess($user)) {
            return true;
        }

        return match ($tab) {
            'my_request' => $user->can('tickets.view'),
            'need_assignment' => $user->can('tickets.assign'),
            'assign_to_me' => $user->can('tickets.approve'),
            'overdue' => $user->can('tickets.assign') || $user->can('tickets.update') || $user->can('tickets.approve'),
            'closed' => $user->can('tickets.update') || $user->can('tickets.approve'),
            'all' => false,
            default => false,
        };
    }

    public function canViewTicket(SysUser $user, Ticket $ticket): bool
    {
        if ($this->hasFullAccess($user)) {
            return true;
        }

        if ($ticket->requester_id === $user->id || $ticket->assigned_to === $user->id) {
            return true;
        }

        if ($this->isPic($user) && $ticket->assigned_to === null) {
            return true;
        }

        if ($this->isSupervisor($user)) {
            $ticket->loadMissing('requester', 'jabatan');

            return $this->sameUnit($user, $ticket->requester?->unit_id)
                || $this->sameUnit($user, $ticket->jabatan?->unit_id);
        }

        return false;
    }

    public function canChangeTicketStatus(SysUser $user, Ticket $ticket): bool
    {
        if ($this->hasFullAccess($user)) {
            return true;
        }

        return $ticket->assigned_to === $user->id
            && $this->roleTokens($user)->contains('picticket');
    }

    public function canManageTickets(SysUser $user): bool
    {
        return $this->hasFullAccess($user);
    }

    public function applyVisibleScope(Builder $query, SysUser $user): Builder
    {
        if ($this->hasFullAccess($user)) {
            return $query;
        }

        if ($this->isSupervisor($user)) {
            return $query->where(function (Builder $visible) use ($user) {
                $visible->where('requester_id', $user->id)
                    ->orWhere('assigned_to', $user->id);

                if ($user->unit_id) {
                    $visible->orWhereHas('requester', fn (Builder $requester) => $requester->where('unit_id', $user->unit_id))
                        ->orWhereHas('jabatan', fn (Builder $jabatan) => $jabatan->where('unit_id', $user->unit_id));
                }
            });
        }

        if ($this->isPic($user)) {
            return $query->where(function (Builder $visible) use ($user) {
                $visible->where('assigned_to', $user->id)
                    ->orWhereNull('assigned_to')
                    ->orWhere('requester_id', $user->id);
            });
        }

        return $query->where('requester_id', $user->id);
    }

    public function applyTabScope(Builder $query, string $tab, SysUser $user): Builder
    {
        abort_unless($this->canAccessTab($user, $tab), 403);

        $this->applyVisibleScope($query, $user);

        return match ($tab) {
            'my_request' => $query->where('requester_id', $user->id),
            'assign_to_me' => $query->where('assigned_to', $user->id),
            'need_assignment' => $query->whereNull('assigned_to'),
            'overdue' => $this->applyOverdueScope($query),
            'closed' => $query->whereIn('status', ['RESOLVED', 'CLOSED']),
            default => $query,
        };
    }

    public function countsFor(SysUser $user): array
    {
        return $this->tabsFor($user)
            ->mapWithKeys(function (array $tab) use ($user) {
                $query = $this->baseQuery();
                $this->applyTabScope($query, $tab['key'], $user);

                return [$tab['key'] => $query->count()];
            })
            ->all();
    }

    public function resolveRequestedTab(SysUser $user, ?string $requested): string
    {
        $tabs = $this->tabsFor($user);
        abort_if($tabs->isEmpty(), 403);

        $default = $this->defaultTab($tabs);
        $tab = $requested ?: $default;

        if (! $tabs->contains('key', $tab)) {
            abort(403);
        }

        return $tab;
    }

    public function defaultTab(Collection $tabs): string
    {
        return $tabs->first()['key'];
    }

    private function baseQuery(): Builder
    {
        return Ticket::query();
    }

    private function applyOverdueScope(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->where(function (Builder $overdue) {
                $overdue->where('is_overdue', true)
                    ->orWhere('resolve_due_at', '<', now())
                    ->orWhere('response_due_at', '<', now());
            });
    }

    private function hasFullAccess(SysUser $user): bool
    {
        return $this->roleTokens($user)->intersect(['admin', 'admin_ticket', 'administrator', 'superadmin', 'super_admin'])->isNotEmpty();
    }

    private function isPic(SysUser $user): bool
    {
        return $user->can('tickets.assign')
            || $this->roleTokens($user)->intersect(['pic', 'picticket', 'pic_ticket', 'ticket_pic'])->isNotEmpty();
    }

    private function isSupervisor(SysUser $user): bool
    {
        return $this->roleTokens($user)->intersect(['supervisor', 'manager', 'project_manager'])->isNotEmpty();
    }

    private function sameUnit(SysUser $user, ?string $unitId): bool
    {
        return $user->unit_id !== null && $unitId !== null && $user->unit_id === $unitId;
    }

    private function roleTokens(SysUser $user): Collection
    {
        $roles = collect();

        if (method_exists($user, 'roles')) {
            $roles = $roles->merge($user->roles()->get(['code', 'name'])->flatMap(fn ($role) => [$role->code ?? null, $role->name ?? null]));
        }

        if ($user->relationLoaded('jabatan') || $user->jabatan) {
            $roles->push($user->jabatan?->code, $user->jabatan?->name);
        }

        return $roles
            ->filter()
            ->map(fn ($role) => Str::of((string) $role)->lower()->replace([' ', '-'], '_')->toString())
            ->unique()
            ->values();
    }
}
