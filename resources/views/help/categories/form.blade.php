@extends('layouts.erp')

@php
    $isEdit = $mode === 'edit';
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.route('help.categories.index').'"><i class="bi bi-arrow-left me-1"></i>Back</a>';
@endphp

@section('content')
    <section class="erp-panel">
        <div class="erp-panel-header"><h2 class="erp-panel-title">{{ $isEdit ? 'Edit' : 'Create' }} Help Category</h2></div>
        <div class="erp-panel-body">
            <form method="POST" action="{{ $isEdit ? route('help.categories.update', $category) : route('help.categories.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="erp-form-grid">
                    <div class="col-span">
                        <label class="form-label">Name</label>
                        <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $category->name) }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Type</label>
                        <select class="form-select js-select2 @error('type') is-invalid @enderror" name="type">
                            @foreach ($types as $type)
                                <option value="{{ $type }}" @selected(old('type', $category->type) === $type)>{{ str_replace('_', ' ', $type) }}</option>
                            @endforeach
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Parent</label>
                        <select class="form-select js-select2 @error('parent_id') is-invalid @enderror" name="parent_id">
                            <option value="">- None -</option>
                            @foreach ($parents->where('id', '!=', $category->id) as $parent)
                                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parent->id)>{{ str_replace('_', ' ', $parent->type) }} - {{ $parent->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Slug</label>
                        <input class="form-control @error('slug') is-invalid @enderror" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="auto-generated">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Icon</label>
                        <input class="form-control @error('icon') is-invalid @enderror" name="icon" value="{{ old('icon', $category->icon) }}" placeholder="bi-book">
                        @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Color</label>
                        <input class="form-control @error('color') is-invalid @enderror" name="color" value="{{ old('color', $category->color) }}" placeholder="#2f6f9f">
                        @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Sort No</label>
                        <input class="form-control @error('sort_no') is-invalid @enderror" type="number" min="0" name="sort_no" value="{{ old('sort_no', $category->sort_no ?? 0) }}">
                        @error('sort_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $category->is_active ?? true))>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('help.categories.index') }}">Cancel</a>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </section>
@endsection
