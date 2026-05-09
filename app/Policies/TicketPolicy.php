<?php

namespace App\Policies;

use App\Models\Master\SysUser;
use App\Models\Ticket;
use App\Services\TicketAccessService;

class TicketPolicy
{
    public function viewAny(SysUser $user): bool
    {
        return $user->can('tickets.view');
    }

    public function view(SysUser $user, Ticket $ticket): bool
    {
        return app(TicketAccessService::class)->canViewTicket($user, $ticket);
    }

    public function create(SysUser $user): bool
    {
        return app(TicketAccessService::class)->canManageTickets($user);
    }

    public function update(SysUser $user, Ticket $ticket): bool
    {
        return app(TicketAccessService::class)->canManageTickets($user);
    }

    public function delete(SysUser $user, Ticket $ticket): bool
    {
        return app(TicketAccessService::class)->canManageTickets($user);
    }

    public function assign(SysUser $user, Ticket $ticket): bool
    {
        return app(TicketAccessService::class)->canManageTickets($user);
    }

    public function changeStatus(SysUser $user, Ticket $ticket): bool
    {
        return app(TicketAccessService::class)->canChangeTicketStatus($user, $ticket);
    }
}
