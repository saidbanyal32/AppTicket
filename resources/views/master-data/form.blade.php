@extends('layouts.erp')

@php
    $isEdit = $mode === 'edit';
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.route($config['route'].'.index').'"><i class="bi bi-arrow-left me-1"></i>Back</a>';
    $hasFileField = collect($config['fields'])->contains(fn ($field) => ($field['type'] ?? null) === 'file');
@endphp

@section('content')
    <section class="erp-panel">
        <div class="erp-panel-header">
            <h2 class="erp-panel-title">{{ $isEdit ? 'Edit' : 'Create' }} {{ $config['title'] }}</h2>
        </div>
        <div class="erp-panel-body">
            <form method="POST" action="{{ $isEdit ? route($config['route'].'.update', $record) : route($config['route'].'.store') }}" @if ($hasFileField) enctype="multipart/form-data" @endif>
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <x-erp.form-section title="Data">
                    @foreach ($config['fields'] as $name => $field)
                        @continue(($field['hide_on_show'] ?? false) && false)
                        @php
                            $type = $field['type'] ?? 'text';
                            $value = $type === 'password' ? old($name, '') : old($name, data_get($record, $name));
                            $inputId = 'field_'.$name;
                            $span = ($field['span'] ?? 1) > 1 ? 'col-span' : '';

                            if ($value instanceof \Illuminate\Support\Carbon && $type === 'datetime-local') {
                                $value = $value->format('Y-m-d\TH:i');
                            }
                        @endphp
                        <div class="{{ $span }}">
                            @if ($type === 'boolean')
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input @error($name) is-invalid @enderror" id="{{ $inputId }}" type="checkbox" name="{{ $name }}" value="1" @checked((bool) $value || (! $record->exists && $value === null))>
                                    <label class="form-check-label" for="{{ $inputId }}">{{ $field['label'] }}</label>
                                    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            @else
                                <label class="form-label" for="{{ $inputId }}">{{ $field['label'] }}</label>

                                @if ($type === 'textarea')
                                    <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $inputId }}" name="{{ $name }}">{{ $value }}</textarea>
                                @elseif ($type === 'select' || $type === 'multi_select')
                                    @php
                                        $selectedValues = $type === 'multi_select'
                                            ? collect(old($name, data_get($record, $field['relation'])?->pluck('id')->all() ?? []))->map(fn ($item) => (string) $item)->all()
                                            : [(string) $value];
                                    @endphp
                                    <select
                                        class="form-select js-select2 @error($name) is-invalid @enderror"
                                        id="{{ $inputId }}"
                                        name="{{ $type === 'multi_select' ? $name.'[]' : $name }}"
                                        @if ($type === 'multi_select') multiple @endif
                                        @if (! empty($field['depends_on'])) data-depends-on="{{ $field['depends_on'] }}" @endif
                                        @if (! empty($field['depends_on_attribute'])) data-depends-on-attribute="{{ $field['depends_on_attribute'] }}" @endif
                                    >
                                        @if ($field['nullable'] ?? false)
                                            <option value="">- None -</option>
                                        @endif
                                        @foreach ($options[$field['relation']] ?? [] as $option)
                                            <option
                                                value="{{ $option['id'] }}"
                                                @foreach ($option['attributes'] ?? [] as $attribute => $attributeValue)
                                                    data-{{ $attribute }}="{{ $attributeValue }}"
                                                @endforeach
                                                @selected(in_array((string) $option['id'], $selectedValues, true))
                                            >{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($type === 'select_static')
                                    <select class="form-select js-select2 @error($name) is-invalid @enderror" id="{{ $inputId }}" name="{{ $name }}">
                                        @foreach ($field['options'] as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($type === 'file')
                                    <input class="form-control @error($name) is-invalid @enderror" id="{{ $inputId }}" type="file" name="{{ $name }}" accept="{{ $field['accept'] ?? null }}">
                                    @if ($record->exists && filled(data_get($record, $name)))
                                        @php($fileUrl = \Illuminate\Support\Facades\Storage::disk($field['disk'] ?? 'public')->url(data_get($record, $name)))
                                        <div class="mt-2">
                                            <img src="{{ $fileUrl }}" alt="{{ $field['label'] }}" class="rounded object-fit-cover border" style="width: 72px; height: 72px;">
                                        </div>
                                    @endif
                                @else
                                    <input class="form-control @error($name) is-invalid @enderror" id="{{ $inputId }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" step="{{ $field['step'] ?? null }}">
                                @endif

                                @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @endif
                        </div>
                    @endforeach
                </x-erp.form-section>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route($config['route'].'.index') }}">Cancel</a>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </section>
@endsection
