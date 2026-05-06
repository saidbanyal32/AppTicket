<div class="erp-toolbar erp-filter-bar js-erp-datatable-filters">
    <input
        class="form-control erp-search js-datatable-keyword"
        type="search"
        name="keyword"
        placeholder="Search {{ $config['title'] }}"
        autocomplete="off"
    >

    @if (collect($config['columns'])->contains(fn ($column) => ($column['type'] ?? null) === 'status'))
        <select class="form-select js-datatable-filter" name="status" aria-label="Status filter">
            <option value="">Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    @endif

    @foreach ($filterFields as $filter)
        <select class="form-select js-datatable-filter" name="{{ $filter['key'] }}" aria-label="{{ $filter['label'] }} filter">
            <option value="">{{ $filter['label'] }}</option>
            @foreach ($options[$filter['relation']] ?? [] as $option)
                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    @endforeach

    <button class="btn btn-sm btn-primary js-datatable-search" type="button">
        <i class="bi bi-search me-1"></i>Search
    </button>
    <button class="btn btn-sm btn-outline-secondary js-datatable-reset" type="button">
        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
    </button>
    <a class="btn btn-sm btn-primary ms-auto" href="{{ route($config['route'].'.create') }}">
        <i class="bi bi-plus-lg me-1"></i>Add
    </a>
</div>
