@extends('layouts.erp')

@php
    $access = app(\App\Services\UiAuthorizationService::class);
    $resourceKey = $access->resourceKeyFromRoute($config['route'] ?? null);
@endphp



@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
    @endif

    <section class="erp-panel">
        @include('partials.erp.datatable-filters')

        <div class="erp-table-wrap">
            <table
                class="table table-hover align-middle js-erp-datatable"
                data-ajax-url="{{ route($config['route'].'.datatable') }}"
                data-erp-columns='@json($datatableColumns)'
                data-can-export="{{ $resourceKey && $access->canResource($resourceKey, 'export') ? '1' : '0' }}"
            >
                <thead>
                    <tr>
                        <th style="width: 42px;" data-priority="1">No</th>
                        @foreach ($config['columns'] as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                        <th style="width: 110px;" data-priority="2">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
@endsection
