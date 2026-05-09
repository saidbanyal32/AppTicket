@extends('layouts.erp')

@php
    $access = app(\App\Services\UiAuthorizationService::class);
    $actions = '<a class="btn btn-sm btn-outline-secondary" href="'.route('help.articles.index').'"><i class="bi bi-arrow-left me-1"></i>Back</a>';
    if ($access->canResource('help', 'update')) {
        $actions .= ' <a class="btn btn-sm btn-primary" href="'.route('help.articles.edit', $article).'"><i class="bi bi-pencil me-1"></i>Edit</a>';
    }
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
    @endif

    <div class="erp-help-layout">
        <aside class="erp-help-sidebar">
            <section class="erp-panel">
                <div class="erp-panel-header"><h2 class="erp-panel-title">Categories</h2></div>
                <div class="erp-panel-body erp-help-category-list">
                    @foreach ($categories->groupBy('type') as $type => $items)
                        <div class="erp-help-category-group">
                            <strong>{{ str_replace('_', ' ', $type) }}</strong>
                            @foreach ($items as $category)
                                <a class="{{ $article->category_id === $category->id ? 'active' : '' }}" href="{{ route('help.articles.index', ['category_id' => $category->id]) }}">
                                    <span><i class="bi {{ $category->icon ?: 'bi-folder2' }}"></i>{{ $category->name }}</span>
                                    <em>{{ number_format($category->articles_count) }}</em>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>

        <main class="erp-help-reader">
            <section class="erp-panel">
                <div class="erp-help-search-band">
                    <form method="GET" action="{{ route('help.articles.index') }}">
                        <i class="bi bi-search"></i>
                        <input class="form-control" name="keyword" value="{{ request('keyword') }}" placeholder="Search documentation">
                    </form>
                </div>
                <div class="erp-panel-body">
                    <div class="erp-help-breadcrumb">
                        <a href="{{ route('help.articles.index') }}">Help Center</a>
                        <i class="bi bi-chevron-right"></i>
                        <span>{{ str_replace('_', ' ', $article->article_type) }}</span>
                        <i class="bi bi-chevron-right"></i>
                        <span>{{ $article->category?->name }}</span>
                    </div>

                    <article class="erp-help-article">
                        <div class="erp-help-article-head">
                            <div>
                                <h2>{{ $article->title }}</h2>
                                @if ($article->short_description)
                                    <p>{{ $article->short_description }}</p>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                @include('help.partials.publish-status', ['published' => $article->is_published])
                                <span class="erp-status">{{ str_replace('_', ' ', $article->visibility) }}</span>
                            </div>
                        </div>

                        @if ($article->thumbnail)
                            <img class="erp-help-thumbnail" src="{{ Storage::disk('public')->url($article->thumbnail) }}" alt="{{ $article->title }}">
                        @endif

                        <div class="erp-help-content">
                            {!! $article->content !!}
                        </div>
                    </article>
                </div>
            </section>
        </main>

        <aside class="d-grid gap-2">
            <section class="erp-panel">
                <div class="erp-panel-header"><h2 class="erp-panel-title">Article Information</h2></div>
                <div class="erp-panel-body erp-info-list">
                    <div><span>Type</span><strong>{{ str_replace('_', ' ', $article->article_type) }}</strong></div>
                    <div><span>Category</span><strong>{{ $article->category?->name ?? '-' }}</strong></div>
                    <div><span>Published At</span><strong>{{ $article->published_at?->format('Y-m-d H:i') ?? '-' }}</strong></div>
                    <div><span>Created By</span><strong>{{ $article->creator?->name ?? '-' }}</strong></div>
                    <div><span>Updated By</span><strong>{{ $article->updater?->name ?? '-' }}</strong></div>
                    <div><span>Updated</span><strong>{{ $article->updated_at?->format('Y-m-d H:i') ?? '-' }}</strong></div>
                </div>
            </section>

            @if ($article->tags)
                <section class="erp-panel">
                    <div class="erp-panel-header"><h2 class="erp-panel-title">Tags</h2></div>
                    <div class="erp-panel-body">
                        @foreach ($article->tags as $tag)
                            <span class="erp-help-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="erp-panel">
                <div class="erp-panel-header"><h2 class="erp-panel-title">Attachments</h2></div>
                <div class="erp-panel-body">
                    @forelse ($article->attachments as $attachment)
                        <a class="erp-attachment" href="{{ $attachment->url }}" target="_blank">
                            <i class="bi bi-paperclip"></i>{{ $attachment->original_name }} <span class="text-muted">({{ $attachment->readable_size }})</span>
                        </a>
                    @empty
                        <div class="text-muted">No attachments.</div>
                    @endforelse
                </div>
            </section>

            <section class="erp-panel">
                <div class="erp-panel-header"><h2 class="erp-panel-title">Related Article</h2></div>
                <div class="erp-panel-body erp-help-related">
                    @forelse ($related as $item)
                        <a href="{{ route('help.articles.show', $item) }}">
                            <strong>{{ $item->title }}</strong>
                            <span>{{ str_replace('_', ' ', $item->article_type) }}</span>
                        </a>
                    @empty
                        <div class="text-muted">No related article.</div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
@endsection
