<header class="erp-topbar">
    <div class="erp-topbar-start">
        <button class="erp-icon-btn erp-topbar-sidebar-toggle js-sidebar-toggle" type="button" aria-controls="erpSidebar" aria-expanded="false" title="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        <form class="erp-global-search" role="search">
            <i class="bi bi-search"></i>
            <input class="form-control" type="search" placeholder="Search or type a command">
        </form>
    </div>

    <div class="erp-topbar-tools">
        @if (app(\App\Services\UiAuthorizationService::class)->canResource('tickets', 'create'))
            <a class="erp-icon-btn" href="{{ route('tickets.create') }}" title="Create Ticket">
                <i class="bi bi-plus-lg"></i>
            </a>
        @endif
        <div class="dropdown">
            <button class="erp-icon-btn position-relative" type="button" data-bs-toggle="dropdown" title="Notifications">
                <i class="bi bi-bell"></i>
                @if (($topbarUnreadCount ?? 0) > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $topbarUnreadCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end erp-notification-menu">
                @forelse (($topbarNotifications ?? collect()) as $notification)
                    <a class="dropdown-item {{ $notification->is_read ? '' : 'fw-semibold' }}" href="{{ $notification->url ?: route('notifications.index') }}">
                        <span class="d-block">{{ $notification->title }}</span>
                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($notification->message, 60) }}</small>
                    </a>
                @empty
                    <span class="dropdown-item-text text-muted">No notifications</span>
                @endforelse
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">View all</a>
            </div>
        </div>
        <button class="erp-icon-btn" type="button" title="Help">
            <i class="bi bi-question-circle"></i>
        </button>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i> {{ auth()->user()?->name ?? 'User' }}
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <span class="dropdown-item-text small text-muted">{{ auth()->user()?->email }}</span>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>
