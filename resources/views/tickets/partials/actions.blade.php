@php
    $access = app(\App\Services\UiAuthorizationService::class);
@endphp

<div class="btn-group">
    @can('view', $ticket)
        <a class="btn btn-sm btn-outline-primary" href="{{ route('tickets.show', $ticket) }}" title="Detail"><i class="bi bi-eye"></i></a>
    @endcan
    @can('update', $ticket)
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('tickets.edit', $ticket) }}" title="Edit"><i class="bi bi-pencil"></i></a>
    @endcan
    @can('delete', $ticket)
        <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('Delete ticket?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
</div>
