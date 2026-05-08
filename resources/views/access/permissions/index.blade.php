@extends('layouts.erp')

@php
    $access = app(\App\Services\UiAuthorizationService::class);
    $actions = '';

    if ($access->canResource('modules', 'view')) {
        $actions .= '<a class="btn btn-sm btn-outline-secondary" href="'.route('master.modules.index').'"><i class="bi bi-grid me-1"></i>Modules</a>';
    }

    if ($access->canResource('actions', 'view')) {
        $actions .= '<a class="btn btn-sm btn-outline-secondary" href="'.route('master.actions.index').'"><i class="bi bi-lightning-charge me-1"></i>Actions</a>';
    }

    if ($access->canResource('permissions', 'create')) {
        $actions .= '<a class="btn btn-sm btn-primary" href="'.route('master.permissions.create').'"><i class="bi bi-magic me-1"></i>Generate</a>';
    }
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
    @endif

    <section class="erp-panel">
        <div class="erp-panel-header">
            <h2 class="erp-panel-title">Generated Permission Catalog</h2>
        </div>
        <div class="erp-panel-body">
            <div class="erp-permission-catalog">
                @foreach ($modules as $module)
                    @php($modulePermissions = $module->permissions->sortBy(fn ($permission) => $permission->action?->sort_no ?? 999))
                    <article class="erp-permission-module">
                        <div class="erp-permission-module-head">
                            <span><i class="bi {{ $module->icon ?: 'bi-grid' }} me-1"></i>{{ $module->name }}</span>
                            <small>{{ $module->slug }}</small>
                        </div>
                        <div class="erp-permission-chips">
                            @forelse ($modulePermissions as $permission)
                                <span class="erp-permission-chip">{{ $permission->permission_slug ?? $permission->name }}</span>
                            @empty
                                <span class="text-muted">No permission generated</span>
                            @endforelse
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
