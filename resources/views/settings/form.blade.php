@extends('layouts.erp')

@php
    $isEdit = $mode === 'edit';
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.route('settings.index').'"><i class="bi bi-arrow-left me-1"></i>Back</a>';
    $isAsset = filled($setting->value) && str_starts_with($setting->value, 'company-assets/');
    $assetUrl = $isAsset ? \Illuminate\Support\Facades\Storage::disk('public')->url($setting->value) : null;
@endphp

@section('content')
    @if (session('status'))<div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>@endif
    <section class="erp-panel">
        <div class="erp-panel-header"><h2 class="erp-panel-title">{{ $isEdit ? 'Edit' : 'Create' }} Setting</h2></div>
        <div class="erp-panel-body">
            <form method="POST" action="{{ $isEdit ? route('settings.update', $setting) : route('settings.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($isEdit) @method('PUT') @endif
                <div class="erp-form-grid">
                    <div><label class="form-label">Key</label><input class="form-control @error('key') is-invalid @enderror" name="key" value="{{ old('key', $setting->key) }}">@error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div><label class="form-label">Type</label><input class="form-control" name="type" value="{{ old('type', $setting->type) }}"></div>
                    <div class="col-span"><label class="form-label">Value</label><textarea class="form-control" name="value" rows="5">{{ old('value', $setting->value) }}</textarea></div>
                    <div class="col-span">
                        <label class="form-label" for="asset_file">Icon/Logo Perusahaan</label>
                        <input class="form-control @error('asset_file') is-invalid @enderror" id="asset_file" type="file" name="asset_file" accept=".jpg,.jpeg,.png,.webp,.svg,.ico,image/*">
                        <div class="form-text">Opsional. Upload akan mengisi kolom value dengan path file. Maksimal 2 MB.</div>
                        @error('asset_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if ($assetUrl)
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <img src="{{ $assetUrl }}" alt="{{ $setting->key }}" class="rounded object-fit-contain border bg-white" style="width: 88px; height: 64px;">
                                <a href="{{ $assetUrl }}" target="_blank" rel="noopener">Lihat file</a>
                            </div>
                        @endif
                    </div>
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
