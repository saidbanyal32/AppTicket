@extends('layouts.erp')

@php
    $isEdit = $mode === 'edit';
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.route('settings.index').'"><i class="bi bi-arrow-left me-1"></i>Back</a>';
@endphp

@section('content')
    @if (session('status'))<div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>@endif
    <section class="erp-panel">
        <div class="erp-panel-header"><h2 class="erp-panel-title">{{ $isEdit ? 'Edit' : 'Create' }} Setting</h2></div>
        <div class="erp-panel-body">
            <form method="POST" action="{{ $isEdit ? route('settings.update', $setting) : route('settings.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif
                <div class="erp-form-grid">
                    <div><label class="form-label">Key</label><input class="form-control @error('key') is-invalid @enderror" name="key" value="{{ old('key', $setting->key) }}">@error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div><label class="form-label">Type</label><input class="form-control" name="type" value="{{ old('type', $setting->type) }}"></div>
                    <div class="col-span"><label class="form-label">Value</label><textarea class="form-control" name="value" rows="5">{{ old('value', $setting->value) }}</textarea></div>
                    <div class="col-span"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ old('description', $setting->description) }}</textarea></div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('settings.index') }}">Cancel</a>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </section>
@endsection
