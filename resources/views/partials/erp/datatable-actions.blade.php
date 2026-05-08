@php
    $access = app(\App\Services\UiAuthorizationService::class);
    $resourceKey = $access->resourceKeyFromRoute($config['route'] ?? null);
@endphp

<span class="erp-table-actions">
    @if ($resourceKey && $access->canResource($resourceKey, 'view'))
        <a class="erp-icon-btn" href="{{ route($config['route'].'.show', $record) }}" title="Detail">
            <i class="bi bi-eye"></i>
        </a>
    @endif
    @if ($resourceKey && $access->canResource($resourceKey, 'update'))
        <a class="erp-icon-btn" href="{{ route($config['route'].'.edit', $record) }}" title="Edit">
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    @if (($config['route'] ?? null) === 'master.users' && auth()->user()?->can('users.update'))
        <form method="POST" action="{{ route('master.users.reset-password', $record) }}" onsubmit="return confirm('Reset password user ini?')">
            @csrf
            <button class="erp-icon-btn" type="submit" title="Reset password">
                <i class="bi bi-key"></i>
            </button>
        </form>
    @endif
    @if ($resourceKey && $access->canResource($resourceKey, 'delete'))
        <form method="POST" action="{{ route($config['route'].'.destroy', $record) }}" onsubmit="return confirm('Delete this record?')">
            @csrf
            @method('DELETE')
            <button class="erp-icon-btn text-danger" type="submit" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif
</span>
