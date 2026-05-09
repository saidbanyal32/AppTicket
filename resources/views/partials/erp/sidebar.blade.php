@php
    $access = app(\App\Services\UiAuthorizationService::class);
    $modules = collect(config('master-data'))
        ->filter(fn ($item) => $access->canMasterConfig($item, 'view'))
        ->groupBy('group')
        ->map(function ($items, $group) {
            return [
                'label' => $group,
                'icon' => match ($group) {
                    'Master Organisasi' => 'bi-diagram-3',
                    'Users & Access' => 'bi-shield-lock',
                    'Master Ticketing' => 'bi-ticket-perforated',
                    default => 'bi-grid-1x2',
                },
                'active' => $items->contains(fn ($item) => request()->routeIs($item['route'].'.*')),
                'children' => $items->map(fn ($item) => [
                    'label' => $item['title'],
                    'url' => route($item['route'].'.index'),
                    'active' => request()->routeIs($item['route'].'.*'),
                ])->values()->all(),
            ];
        });

    $sidebarSections = [
        [
            'title' => 'Transaction',
            'custom' => [
                [
                    'label' => 'Tickets',
                    'icon' => 'bi-ticket-detailed',
                    'url' => route('tickets.index'),
                    'active' => request()->routeIs('tickets.*'),
                    'permission' => config('access.menu.custom.tickets.permission'),
                ],
                [
                    'label' => 'Help Center',
                    'icon' => 'bi-life-preserver',
                    'url' => route('help.articles.index'),
                    'active' => request()->routeIs('help.*'),
                    'permission' => config('access.menu.custom.help.permission'),
                    'children' => [
                        ['label' => 'User Guide', 'url' => route('help.articles.index', ['article_type' => 'USER_GUIDE']), 'active' => request('article_type') === 'USER_GUIDE'],
                        ['label' => 'Developer Docs', 'url' => route('help.articles.index', ['article_type' => 'DEVELOPER_DOCS']), 'active' => request('article_type') === 'DEVELOPER_DOCS'],
                        ['label' => 'FAQ', 'url' => route('help.articles.index', ['article_type' => 'FAQ']), 'active' => request('article_type') === 'FAQ'],
                        ['label' => 'Troubleshooting', 'url' => route('help.articles.index', ['article_type' => 'TROUBLESHOOTING']), 'active' => request('article_type') === 'TROUBLESHOOTING'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Data Master',
            'groups' => ['Master Organisasi'],
        ],
        [
            'title' => 'Master Ticketing',
            'groups' => ['Master Ticketing'],
        ],
        [
            'title' => 'System',
            'custom' => [
                [
                    'label' => 'Notifications',
                    'icon' => 'bi-bell',
                    'url' => route('notifications.index'),
                    'active' => request()->routeIs('notifications.*'),
                    'permission' => config('access.menu.custom.notifications.permission'),
                ],
                [
                    'label' => 'Settings',
                    'icon' => 'bi-gear',
                    'url' => route('settings.index'),
                    'active' => request()->routeIs('settings.*'),
                    'permission' => config('access.menu.custom.settings.permission'),
                ],
            ],
        ],
        [
            'title' => 'Users & Access',
            'groups' => ['Users & Access'],
            'labels' => ['Users & Access' => 'Users & Access'],
        ],
    ];
@endphp

<aside class="erp-sidebar" aria-label="ERP navigation" id="erpSidebar">
    <div class="erp-sidebar-logo">
        <button class="erp-logo-toggle js-sidebar-toggle" type="button" aria-controls="erpSidebar" aria-expanded="false" title="Toggle sidebar">
            <span class="erp-logo-mark">ZE</span>
            <i class="bi bi-list erp-sidebar-toggle-icon" aria-hidden="true"></i>
        </button>
        <span class="erp-logo-text">
            <span class="erp-logo-title">SupportDesk Pro</span>
            <span class="erp-logo-subtitle">Integrated Support & Ticketing Platform</span>
        </span>
    </div>

    <nav class="erp-nav">
        @foreach ($sidebarSections as $sectionIndex => $section)
            @php
                $visibleCustom = collect($section['custom'] ?? [])
                    ->filter(fn ($item) => $access->canAny($item['permission'] ?? null))
                    ->values();
                $visibleGroups = collect($section['groups'] ?? [])
                    ->filter(fn ($group) => ! empty($modules->get($group)['children'] ?? []))
                    ->values();
            @endphp

            @continue($visibleCustom->isEmpty() && $visibleGroups->isEmpty())

            <div class="erp-nav-group {{ $sectionIndex > 0 ? 'has-gap' : '' }}">
                <div class="erp-nav-section">{{ $section['title'] }}</div>

                @foreach ($visibleCustom as $item)
                    @if (! empty($item['children']))
                        <a class="erp-nav-link {{ !empty($item['active']) ? 'active' : '' }}"
                           data-bs-toggle="collapse"
                           href="#erpCustomNav{{ $sectionIndex }}{{ $loop->index }}"
                           role="button"
                           aria-expanded="{{ !empty($item['active']) ? 'true' : 'false' }}"
                           aria-controls="erpCustomNav{{ $sectionIndex }}{{ $loop->index }}"
                           title="{{ $item['label'] }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span class="erp-nav-label">{{ $item['label'] }}</span>
                            <i class="bi {{ !empty($item['active']) ? 'bi-chevron-down' : 'bi-chevron-right' }} erp-nav-chevron"></i>
                        </a>
                        <div class="collapse {{ !empty($item['active']) ? 'show' : '' }}" id="erpCustomNav{{ $sectionIndex }}{{ $loop->index }}">
                            <div class="erp-subnav">
                                @foreach ($item['children'] as $child)
                                    <a class="{{ !empty($child['active']) ? 'active' : '' }}" href="{{ $child['url'] }}">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a class="erp-nav-link {{ !empty($item['active']) ? 'active' : '' }}" href="{{ $item['url'] }}" title="{{ $item['label'] }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span class="erp-nav-label">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach

                @foreach ($visibleGroups as $group)
                    @php
                        $module = $modules->get($group);
                        $moduleLabel = $section['labels'][$group] ?? $group;
                    @endphp

                    @continue(empty($module))

                    <a class="erp-nav-link {{ !empty($module['active']) ? 'active' : '' }}"
                       data-bs-toggle="collapse"
                       href="#erpNav{{ $sectionIndex }}{{ $loop->index }}"
                       role="button"
                       aria-expanded="{{ !empty($module['active']) ? 'true' : 'false' }}"
                       aria-controls="erpNav{{ $sectionIndex }}{{ $loop->index }}"
                       title="{{ $moduleLabel }}">
                        <i class="bi {{ $module['icon'] }}"></i>
                        <span class="erp-nav-label">{{ $moduleLabel }}</span>
                        <i class="bi {{ !empty($module['active']) ? 'bi-chevron-down' : 'bi-chevron-right' }} erp-nav-chevron"></i>
                    </a>
                    <div class="collapse {{ !empty($module['active']) ? 'show' : '' }}" id="erpNav{{ $sectionIndex }}{{ $loop->index }}">
                        <div class="erp-subnav">
                            @foreach ($module['children'] as $child)
                                <a class="{{ !empty($child['active']) ? 'active' : '' }}" href="{{ $child['url'] }}">{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                </div>
        @endforeach
    </nav>
</aside>
