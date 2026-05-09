@php($access = app(\App\Services\UiAuthorizationService::class))
<div class="erp-table-actions">
    @if ($access->canResource('help', 'update'))
        <a class="btn btn-sm btn-outline-primary" href="{{ route('help.categories.edit', $category) }}" title="Edit"><i class="bi bi-pencil"></i></a>
    @endif
    @if ($access->canResource('help', 'delete'))
        <form method="POST" action="{{ route('help.categories.destroy', $category) }}" onsubmit="return confirm('Hapus category ini?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
        </form>
    @endif
</div>
