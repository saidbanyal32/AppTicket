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
            <form class="erp-readonly-form">
                <x-erp.form-section title="Data">
                @foreach ($config['fields'] as $name => $field)
                    @continue($field['hide_on_show'] ?? false)
                    @php
                        $type = $field['type'] ?? 'text';
                        $value = data_get($record, $name);
                        $inputId = 'field_'.$name;
                        $span = ($field['span'] ?? 1) > 1 ? 'col-span' : '';

                        if ($value instanceof \Illuminate\Support\Carbon) {
                            $value = $value->format('Y-m-d');
                        }
                    @endphp
                    <div class="{{ $span }}">
                        @if ($type === 'boolean')
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" id="{{ $inputId }}" type="checkbox" @checked((bool) $value) disabled>
                                <label class="form-check-label" for="{{ $inputId }}">{{ $field['label'] }}</label>
                                <span class="erp-readonly-status {{ $value ? 'success' : 'danger' }}">{{ $value ? 'Active' : 'Inactive' }}</span>
                            </div>
                        @else
                            <label class="form-label" for="{{ $inputId }}">{{ $field['label'] }}</label>

                            @if ($type === 'textarea')
                                <textarea class="form-control erp-readonly-control" id="{{ $inputId }}" readonly>{{ filled($value) ? $value : '-' }}</textarea>
                            @elseif ($type === 'select')
                                <select class="form-select js-select2 erp-readonly-control" id="{{ $inputId }}" disabled>
                                    @if ($field['nullable'] ?? false)
                                        <option value="">- None -</option>
                                    @elseif (blank($value))
                                        <option value="" selected>-</option>
                                    @endif
                                    @foreach ($options[$field['relation']] ?? [] as $option)
                                        <option value="{{ $option['id'] }}" @selected((string) $value === (string) $option['id'])>{{ $option['label'] }}</option>
                                    @endforeach
                                    @if (filled($value) && ! collect($options[$field['relation']] ?? [])->contains('id', $value))
                                        <option value="{{ $value }}" selected>{{ $value }}</option>
                                    @endif
                                </select>
                            @elseif ($type === 'select_static')
                                <select class="form-select js-select2 erp-readonly-control" id="{{ $inputId }}" disabled>
                                    @if (blank($value))
                                        <option value="" selected>-</option>
                                    @endif
                                    @foreach ($field['options'] as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                    @endforeach
                                    @if (filled($value) && ! array_key_exists($value, $field['options']))
                                        <option value="{{ $value }}" selected>{{ $value }}</option>
                                    @endif
                                </select>
                            @else
                                <input class="form-control erp-readonly-control" id="{{ $inputId }}" type="text" value="{{ filled($value) ? $value : '-' }}" readonly>
                            @endif
                        @endif
                    </div>
                @endforeach
                </x-erp.form-section>
            </form>
        </div>
    </section>
@endsection
