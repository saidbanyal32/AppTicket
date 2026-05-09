@extends('layouts.erp')

@php
    $isEdit = $mode === 'edit';
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.($article->exists ? route('help.articles.show', $article) : route('help.articles.index')).'"><i class="bi bi-arrow-left me-1"></i>Back</a>';
@endphp

@section('content')
    <section class="erp-panel">
        <div class="erp-panel-header"><h2 class="erp-panel-title">{{ $isEdit ? 'Edit' : 'Create' }} Help Article</h2></div>
        <div class="erp-panel-body">
            <form method="POST" action="{{ $isEdit ? route('help.articles.update', $article) : route('help.articles.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="erp-form-grid">
                    <div class="col-span">
                        <label class="form-label">Title</label>
                        <input class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $article->title) }}">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Category</label>
                        <select class="form-select js-select2 @error('category_id') is-invalid @enderror" name="category_id">
                            <option value="">- Select -</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $article->category_id) === (string) $category->id)>{{ str_replace('_', ' ', $category->type) }} - {{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Slug</label>
                        <input class="form-control @error('slug') is-invalid @enderror" name="slug" value="{{ old('slug', $article->slug) }}" placeholder="auto-generated">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-span">
                        <label class="form-label">Short Description</label>
                        <textarea class="form-control @error('short_description') is-invalid @enderror" name="short_description" rows="3">{{ old('short_description', $article->short_description) }}</textarea>
                        @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Article Type</label>
                        <select class="form-select js-select2 @error('article_type') is-invalid @enderror" name="article_type">
                            @foreach ($articleTypes as $type)
                                <option value="{{ $type }}" @selected(old('article_type', $article->article_type) === $type)>{{ str_replace('_', ' ', $type) }}</option>
                            @endforeach
                        </select>
                        @error('article_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Visibility</label>
                        <select class="form-select js-select2 @error('visibility') is-invalid @enderror" name="visibility">
                            @foreach ($visibilities as $visibility)
                                <option value="{{ $visibility }}" @selected(old('visibility', $article->visibility) === $visibility)>{{ str_replace('_', ' ', $visibility) }}</option>
                            @endforeach
                        </select>
                        @error('visibility')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Tags</label>
                        <input class="form-control @error('tags') is-invalid @enderror" name="tags" value="{{ old('tags', $article->tagList()) }}" placeholder="setup, queue, api">
                        @error('tags')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Thumbnail</label>
                        <input class="form-control @error('thumbnail') is-invalid @enderror" type="file" name="thumbnail" accept="image/*">
                        @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Attachments</label>
                        <input class="form-control @error('attachments.*') is-invalid @enderror" type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.zip,.txt">
                        @error('attachments.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_published" value="1" id="isPublished" @checked(old('is_published', $article->is_published ?? true))>
                            <label class="form-check-label" for="isPublished">Published</label>
                        </div>
                    </div>
                    <div class="col-span">
                        <label class="form-label">Content</label>
                        <div class="erp-help-editor @error('content') is-invalid @enderror">
                            <div class="erp-help-editor-toolbar">
                                <button type="button" data-help-command="formatBlock" data-help-value="H2" title="Heading"><i class="bi bi-type-h2"></i></button>
                                <button type="button" data-help-command="formatBlock" data-help-value="P" title="Paragraph"><i class="bi bi-text-paragraph"></i></button>
                                <button type="button" data-help-command="bold" title="Bold"><i class="bi bi-type-bold"></i></button>
                                <button type="button" data-help-command="italic" title="Italic"><i class="bi bi-type-italic"></i></button>
                                <button type="button" data-help-command="insertOrderedList" title="Ordered List"><i class="bi bi-list-ol"></i></button>
                                <button type="button" data-help-command="insertUnorderedList" title="Unordered List"><i class="bi bi-list-ul"></i></button>
                                <button type="button" data-help-command="createLink" title="Link"><i class="bi bi-link-45deg"></i></button>
                                <button type="button" data-help-command="insertImage" title="Image"><i class="bi bi-image"></i></button>
                                <button type="button" data-help-command="insertTable" title="Table"><i class="bi bi-table"></i></button>
                                <button type="button" data-help-command="insertCode" title="Code Block"><i class="bi bi-code-square"></i></button>
                            </div>
                            <div class="erp-help-editor-area js-help-editor" contenteditable="true">{!! old('content', $article->content) !!}</div>
                            <textarea class="d-none js-help-editor-input" name="content">{{ old('content', $article->content) }}</textarea>
                        </div>
                        @error('content')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if ($article->exists && $article->attachments->isNotEmpty())
                    <div class="erp-form-section">
                        <h3 class="erp-section-title">Existing Attachments</h3>
                        @foreach ($article->attachments as $attachment)
                            <span class="erp-attachment">
                                <i class="bi bi-paperclip"></i>{{ $attachment->original_name }}
                                <button class="btn btn-sm btn-link text-danger p-0 ms-1" form="deleteAttachment{{ $attachment->id }}" title="Delete" type="submit"><i class="bi bi-x-lg"></i></button>
                            </span>
                        @endforeach
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('help.articles.index') }}">Cancel</a>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-save me-1"></i>Save</button>
                </div>
            </form>

            @if ($article->exists)
                @foreach ($article->attachments as $attachment)
                    <form id="deleteAttachment{{ $attachment->id }}" method="POST" action="{{ route('help.articles.attachments.destroy', [$article, $attachment]) }}" onsubmit="return confirm('Hapus attachment ini?')">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            @endif
        </div>
    </section>
@endsection
