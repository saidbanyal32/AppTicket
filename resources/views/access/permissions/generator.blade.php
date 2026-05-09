@extends('layouts.erp')

@php
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.route('master.permissions.index').'"><i class="bi bi-arrow-left me-1"></i>Back</a>';
@endphp

@section('content')
    <section class="erp-panel">
        <div class="erp-panel-header">
            <h2 class="erp-panel-title">Permission Generator</h2>
        </div>
        <div class="erp-panel-body">
            <form method="POST" action="{{ route('master.permissions.store') }}" class="erp-generator-form">
                @csrf

                <x-erp.form-section title="Module">
                    <div class="col-span">
                        <label class="form-label" for="module_id">Module</label>
                        <select class="form-select js-select2 js-permission-module @error('module_id') is-invalid @enderror" id="module_id" name="module_id" required>
                            <option value="">Select module</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->id }}" data-module-slug="{{ $module->slug }}" @selected(old('module_id') === $module->id)>{{ $module->name }} ({{ $module->slug }})</option>
                            @endforeach
                        </select>
                        @error('module_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </x-erp.form-section>

                <x-erp.form-section title="Global Actions">
                    <div class="col-span erp-action-checkgrid">
                        @foreach ($permissionActions as $action)
                            <label class="erp-check-tile">
                                <input class="form-check-input" type="checkbox" name="action_ids[]" value="{{ $action->id }}" @checked(in_array((string) $action->id, array_map('strval', old('action_ids', [])), true))>
                                <span>
                                    <strong>{{ $action->name }}</strong>
                                    <small>{{ $action->slug }}</small>
                                </span>
                            </label>
                        @endforeach
                        @error('action_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </x-erp.form-section>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('master.permissions.index') }}">Cancel</a>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-magic me-1"></i> Generate Permission</button>
                </div>
            </form>
        </div>
    </section>
@endsection
