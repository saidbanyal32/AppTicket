@if (!empty($breadcrumbs))
    <nav class="erp-breadcrumb" aria-label="breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)
            @if (!$loop->first)
                <i class="bi bi-chevron-right"></i>
            @endif
            @if (!empty($breadcrumb['url']))
                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
            @else
                <span>{{ $breadcrumb['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif

<div class="erp-page-head">
    <div class="erp-title-group">
        <h1>{{ $title }}</h1>
        @if (!empty($subtitle))
            <div class="erp-title-meta">{{ $subtitle }}</div>
        @endif
    </div>

    @if (!empty($actions))
        <div class="erp-actions">
            {!! $actions !!}
        </div>
    @endif
</div>
