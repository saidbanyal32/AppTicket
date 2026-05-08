<?php

namespace App\Policies;

use App\Models\Master\SysUser;
use App\Models\Ticket;
use App\Services\TicketAccessService;

class TicketPolicy
{
    public function viewAny(SysUser $user): bool
    {
        return app(TicketAccessService::class)->tabsFor($user)->isNotEmpty();
    }

    public function view(SysUser $user, Ticket $ticket): bool
    {
        return app(TicketAccessService::class)->canViewTicket($user, $ticket);
    }

    public function create(SysUser $user): bool
    {
        return $user->can('tickets.create') || $user->can(TicketAccessService::TAB_PERMISSIONS['my_request']);
    }

    public function update(SysUser $user, Ticket $ticket): bool
    {
        return $user->can('tickets.update') && app(TicketAccessService::class)->canViewTicket($user, $ticket);
    }

    public function delete(SysUser $user, Ticket $ticket): bool
    {
        return $user->can('tickets.delete') && app(TicketAccessService::class)->canViewTicket($user, $ticket);
    }

    public function assign(SysUser $user, Ticket $ticket): bool
    {
        return $user->can('tickets.assign') && app(TicketAccessService::class)->canViewTicket($user, $ticket);
    }

    public function changeStatus(SysUser $user, Ticket $ticket): bool
    {
        return ($user->can('tickets.update') || $user->can('tickets.approve'))
            && app(TicketAccessService::class)->canViewTicket($user, $ticket);
    }
}
