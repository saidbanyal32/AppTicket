@extends('layouts.erp')

@php
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.route($config['route'].'.index').'"><i class="bi bi-arrow-left me-1"></i>Back</a><a class="btn btn-sm btn-primary" href="'.route($config['route'].'.edit', $record).'"><i class="bi bi-pencil me-1"></i>Edit</a>';
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
    @endif

    <section class="erp-panel">
        <div class="erp-panel-header">
            <h2 class="erp-panel-title">Detail {{ $config['title'] }}</h2>
        </div>
        <div class="erp-panel-body">
            <div class="erp-detail-grid">
                @foreach ($config['fields'] as $name => $field)
                    @continue($field['hide_on_show'] ?? false)
                    @php
                        $value = data_get($record, $name);
                        if (($field['type'] ?? null) === 'select' && isset($field['relation'])) {
                            $selected = collect($options[$field['relation']] ?? [])->firstWhere('id', $value);
                            $value = $selected['label'] ?? null;
                        }
                        if (($field['type'] ?? null) === 'select_static') {
                            $value = $field['options'][$value] ?? $value;
                        }
                    @endphp
                    <div class="erp-detail-item {{ ($field['span'] ?? 1) > 1 ? 'col-span' : '' }}">
                        <div class="erp-detail-label">{{ $field['label'] }}</div>
                        <div class="erp-detail-value">
                            @if (($field['type'] ?? null) === 'boolean')
                                <span class="erp-status {{ $value ? 'success' : 'danger' }}">{{ $value ? 'Active' : 'Inactive' }}</span>
                            @elseif ($value instanceof \Illuminate\Support\Carbon)
                                {{ $value->format('Y-m-d') }}
                            @else
                                {{ filled($value) ? $value : '-' }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
