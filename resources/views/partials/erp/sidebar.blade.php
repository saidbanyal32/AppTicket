@php
    $modules = collect(config('master-data'))
        ->groupBy('group')
        ->map(function ($items, $group) {
            return [
                'label' => $group,
                'icon' => match ($group) {
                    'Master Organisasi' => 'bi-diagram-3',
                    'Users & Akses' => 'bi-shield-lock',
                    'Item / Material' => 'bi-box-seam',
                    'Project' => 'bi-building',
                    'Vendor' => 'bi-truck',
                    default => 'bi-grid-1x2',
                },
                'active' => $items->contains(fn ($item) => request()->routeIs($item['route'].'.*')),
                'children' => $items->map(fn ($item) => [
                    'label' => $item['title'],
                    'url' => route($item['route'].'.index'),
                    'active' => request()->routeIs($item['route'].'.*'),
                ])->values()->all(),
            ];
        })->values()->all();
@endphp

<aside class="erp-sidebar" aria-label="ERP navigation">
    <div class="erp-sidebar-logo">
        <span class="erp-logo-mark">ZE</span>
        <span class="erp-logo-text">
            <span class="erp-logo-title">APP Ticketing System</span>
            <span class="erp-logo-subtitle">Enterprise Workspace</span>
        </span>
    </div>

    <nav class="erp-nav">
        <div class="erp-nav-section">Modules</div>

        @foreach ($modules as $index => $module)
            <a class="erp-nav-link {{ !empty($module['active']) ? 'active' : '' }}"
               data-bs-toggle="collapse"
               href="#erpNav{{ $index }}"
               role="button"
               aria-expanded="{{ !empty($module['active']) ? 'true' : 'false' }}"
               aria-controls="erpNav{{ $index }}"
               title="{{ $module['label'] }}">
                <i class="bi {{ $module['icon'] }}"></i>
                <span class="erp-nav-label">{{ $module['label'] }}</span>
                <i class="bi {{ !empty($module['active']) ? 'bi-chevron-down' : 'bi-chevron-right' }} erp-nav-chevron"></i>
            </a>
            <div class="collapse {{ !empty($module['active']) ? 'show' : '' }}" id="erpNav{{ $index }}">
                <div class="erp-subnav">
                    @foreach ($module['children'] as $child)
                        <a class="{{ !empty($child['active']) ? 'active' : '' }}" href="{{ $child['url'] }}">{{ $child['label'] }}</a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>
