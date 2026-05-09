@php($access = app(\App\Services\UiAuthorizationService::class))
<div class="erp-table-actions">
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('help.articles.show', $article) }}" title="Preview"><i class="bi bi-eye"></i></a>
    @if ($access->canResource('help', 'update'))
        <a class="btn btn-sm btn-outline-primary" href="{{ route('help.articles.edit', $article) }}" title="Edit"><i class="bi bi-pencil"></i></a>
    @endif
    @if ($access->canResource('help', 'publish'))
        <form method="POST" action="{{ route('help.articles.publish', $article) }}">
            @csrf
            <button class="btn btn-sm btn-outline-warning" type="submit" title="{{ $article->is_published ? 'Unpublish' : 'Publish' }}">
                <i class="bi {{ $article->is_published ? 'bi-eye-slash' : 'bi-upload' }}"></i>
            </button>
        </form>
    @endif
    @if ($access->canResource('help', 'delete'))
        <form method="POST" action="{{ route('help.articles.destroy', $article) }}" onsubmit="return confirm('Hapus article ini?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
        </form>
    @endif
</div>
