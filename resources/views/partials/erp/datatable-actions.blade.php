<span class="erp-table-actions">
    <a class="erp-icon-btn" href="{{ route($config['route'].'.show', $record) }}" title="Detail">
        <i class="bi bi-eye"></i>
    </a>
    <a class="erp-icon-btn" href="{{ route($config['route'].'.edit', $record) }}" title="Edit">
        <i class="bi bi-pencil"></i>
    </a>
    <form method="POST" action="{{ route($config['route'].'.destroy', $record) }}" onsubmit="return confirm('Delete this record?')">
        @csrf
        @method('DELETE')
        <button class="erp-icon-btn text-danger" type="submit" title="Delete">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</span>
