@extends('layouts.erp')

@php
    $access = app(\App\Services\UiAuthorizationService::class);
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.route('help.categories.index').'"><i class="bi bi-folder2-open me-1"></i>Categories</a>';
    if ($access->canResource('help', 'create')) {
        $actions .= ' <a class="btn btn-sm btn-primary" href="'.route('help.articles.create').'"><i class="bi bi-plus-lg me-1"></i>Create Article</a>';
    }
    $columns = [
        ['data' => 'title', 'name' => 'title', 'label' => 'Title'],
        ['data' => 'category', 'name' => 'category_id', 'label' => 'Category'],
        ['data' => 'article_type', 'name' => 'article_type', 'label' => 'Type'],
        ['data' => 'visibility', 'name' => 'visibility', 'label' => 'Visibility'],
        ['data' => 'status', 'name' => 'is_published', 'label' => 'Status'],
        ['data' => 'updated_at', 'name' => 'updated_at', 'label' => 'Updated'],
    ];
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
    @endif

    <div class="erp-summary-grid mb-2">
        @foreach ($summary as $label => $count)
            <section class="erp-panel erp-summary-card">
                <div class="text-muted">{{ $label }}</div>
                <strong>{{ number_format($count) }}</strong>
            </section>
        @endforeach
    </div>

    <section class="erp-panel">
        <div class="erp-toolbar erp-filter-bar js-erp-datatable-filters">
            <input class="form-control erp-search js-datatable-keyword" type="search" value="{{ request('keyword') }}" placeholder="Search title, content, tags">
            <select class="form-select js-select2 js-datatable-filter" name="category_id">
                <option value="">All Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ str_replace('_', ' ', $category->type) }} - {{ $category->name }}</option>
                @endforeach
            </select>
            <select class="form-select js-select2 js-datatable-filter" name="article_type">
                <option value="">All Type</option>
                @foreach ($articleTypes as $type)
                    <option value="{{ $type }}" @selected(request('article_type') === $type)>{{ str_replace('_', ' ', $type) }}</option>
                @endforeach
            </select>
            <select class="form-select js-select2 js-datatable-filter" name="visibility">
                <option value="">All Visibility</option>
                @foreach ($visibilities as $visibility)
                    <option value="{{ $visibility }}" @selected(request('visibility') === $visibility)>{{ str_replace('_', ' ', $visibility) }}</option>
                @endforeach
            </select>
            <select class="form-select js-datatable-filter" name="is_published">
                <option value="">All Status</option>
                <option value="1">Published</option>
                <option value="0">Draft</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary js-datatable-reset" type="button"><i class="bi bi-arrow-counterclockwise"></i></button>
        </div>

        <div class="erp-table-wrap">
            <table class="table table-hover align-middle js-erp-datatable" data-ajax-url="{{ route('help.articles.datatable') }}" data-erp-columns='@json($columns)' data-can-export="{{ $access->canResource('help', 'export') ? '1' : '0' }}">
                <thead>
                    <tr>
                        <th style="width:42px">No</th>
                        @foreach ($columns as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                        <th style="width:130px">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
@endsection
