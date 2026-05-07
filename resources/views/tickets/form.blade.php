@extends('layouts.erp')

@php
    $isEdit = $mode === 'edit';
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.($ticket->exists ? route('tickets.show', $ticket) : route('tickets.index')).'"><i class="bi bi-arrow-left me-1"></i>Back</a>';
@endphp

@section('content')
    <section class="erp-panel">
        <div class="erp-panel-header"><h2 class="erp-panel-title">{{ $isEdit ? 'Edit' : 'Create' }} Ticket</h2></div>
        <div class="erp-panel-body">
            <form method="POST" action="{{ $isEdit ? route('tickets.update', $ticket) : route('tickets.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="erp-form-grid">
                    <div class="col-span">
                        <label class="form-label">Subject</label>
                        <input class="form-control @error('subject') is-invalid @enderror" name="subject" value="{{ old('subject', $ticket->subject) }}">
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Category</label>
                        <select class="form-select js-select2 @error('category_id') is-invalid @enderror" name="category_id">
                            <option value="">- Select -</option>
                            @foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $ticket->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Priority</label>
                        <select class="form-select js-select2 @error('priority') is-invalid @enderror" name="priority">
                            @foreach (\App\Models\Ticket::PRIORITIES as $priority)<option value="{{ $priority }}" @selected(old('priority', $ticket->priority) === $priority)>{{ $priority }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-span">
                        <label class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="6">{{ old('description', $ticket->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Jabatan</label>
                        <select class="form-select js-select2" name="jabatan_id">
                            <option value="">- None -</option>
                            @foreach ($jabatan as $item)<option value="{{ $item->id }}" @selected((string) old('jabatan_id', $ticket->jabatan_id) === (string) $item->id)>{{ $item->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Attachment</label>
                        <input class="form-control @error('attachments.*') is-invalid @enderror" type="file" name="attachments[]" multiple>
                        @error('attachments.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('tickets.index') }}">Cancel</a>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </section>
@endsection
