@extends('layouts.erp')

@php
    $actions = '<a class="btn btn-sm btn-primary" href="'.route('tickets.create').'"><i class="bi bi-plus-lg me-1"></i>Create Ticket</a>';
    $columns = [
        ['data' => 'ticket_no', 'name' => 'ticket_no', 'label' => 'Ticket No'],
        ['data' => 'subject', 'name' => 'subject', 'label' => 'Subject'],
        ['data' => 'category', 'name' => 'category_id', 'label' => 'Category'],
        ['data' => 'priority', 'name' => 'priority', 'label' => 'Priority'],
        ['data' => 'status', 'name' => 'status', 'label' => 'Status'],
        ['data' => 'requester', 'name' => 'requester_id', 'label' => 'Requester'],
        ['data' => 'assignee', 'name' => 'assigned_to', 'label' => 'Assigned To'],
        ['data' => 'created_at', 'name' => 'created_at', 'label' => 'Created At'],
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
            <input class="form-control erp-search js-datatable-keyword" type="search" placeholder="Quick search">
            <select class="form-select js-select2 js-datatable-filter" name="status"><option value="">All Status</option>@foreach (\App\Models\Ticket::STATUSES as $status)<option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>@endforeach</select>
            <select class="form-select js-select2 js-datatable-filter" name="priority"><option value="">All Priority</option>@foreach (\App\Models\Ticket::PRIORITIES as $priority)<option value="{{ $priority }}">{{ $priority }}</option>@endforeach</select>
            <select class="form-select js-select2 js-datatable-filter" name="category_id"><option value="">All Category</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select>
            <select class="form-select js-select2 js-datatable-filter" name="requester_id"><option value="">Requester</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select>
            <select class="form-select js-select2 js-datatable-filter" name="assigned_to"><option value="">Assigned User</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select>
            <select class="form-select js-select2 js-datatable-filter" name="jabatan_id"><option value="">Jabatan</option>@foreach ($jabatan as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select>
            <input class="form-control js-datatable-filter" type="date" name="date_from">
            <input class="form-control js-datatable-filter" type="date" name="date_to">
            <button class="btn btn-sm btn-outline-secondary js-datatable-reset" type="button"><i class="bi bi-arrow-counterclockwise"></i></button>
        </div>

        <div class="erp-table-wrap">
            <table class="table table-hover align-middle js-erp-datatable" data-ajax-url="{{ route('tickets.datatable') }}" data-erp-columns='@json($columns)'>
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
