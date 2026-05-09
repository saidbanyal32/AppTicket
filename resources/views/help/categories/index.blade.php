@extends('layouts.erp')

@php
    $access = app(\App\Services\UiAuthorizationService::class);
    $actions = $access->canResource('help', 'create')
        ? '<a class="btn btn-sm btn-primary" href="'.route('help.categories.create').'"><i class="bi bi-plus-lg me-1"></i>Create Category</a>'
        : '';
    $columns = [
        ['data' => 'name', 'name' => 'name', 'label' => 'Name'],
        ['data' => 'type', 'name' => 'type', 'label' => 'Type'],
        ['data' => 'parent', 'name' => 'parent_id', 'label' => 'Parent'],
        ['data' => 'articles_count', 'name' => 'articles_count', 'label' => 'Articles'],
        ['data' => 'sort_no', 'name' => 'sort_no', 'label' => 'Sort'],
        ['data' => 'status', 'name' => 'is_active', 'label' => 'Status'],
    ];
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
    @endif

    <section class="erp-panel">
        <div class="erp-toolbar erp-filter-bar js-erp-datatable-filters">
            <input class="form-control erp-search js-datatable-keyword" type="search" placeholder="Search category">
            <select class="form-select js-select2 js-datatable-filter" name="type">
                <option value="">All Type</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                @endforeach
            </select>
            <select class="form-select js-datatable-filter" name="is_active">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary js-datatable-reset" type="button"><i class="bi bi-arrow-counterclockwise"></i></button>
        </div>

        <div class="erp-table-wrap">
            <table class="table table-hover align-middle js-erp-datatable" data-ajax-url="{{ route('help.categories.datatable') }}" data-erp-columns='@json($columns)' data-can-export="{{ $access->canResource('help', 'export') ? '1' : '0' }}">
                <thead>
                    <tr>
                        <th style="width:42px">No</th>
                        @foreach ($columns as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                        <th style="width:110px">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
@endsection
