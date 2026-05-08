@extends('layouts.erp')

@php
    $access = app(\App\Services\UiAuthorizationService::class);
    $actions = '';

    if ($access->canResource('actions', 'view')) {
        $actions .= '<a class="btn btn-sm btn-outline-secondary" href="'.route('master.actions.index').'"><i class="bi bi-lightning-charge me-1"></i>Actions</a>';
    }

    if ($access->canResource('permissions', 'create')) {
        $actions .= '<a class="btn btn-sm btn-primary" href="'.route('master.permissions.create').'"><i class="bi bi-magic me-1"></i>Generate Permissions</a>';
    }
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
    @endif

    <section class="erp-panel erp-role-permission-page">
        <div class="erp-panel-header">
            <div>
                <h2 class="erp-panel-title">Role Permission Matrix</h2>
                <small class="text-muted">Global CRUD tetap ringkas, akses workflow dikelompokkan per module.</small>
            </div>
        </div>
        <div class="erp-panel-body">
            <form method="GET" action="{{ route('master.role-permissions.index') }}" class="erp-toolbar mb-2">
                <select class="form-select js-select2" name="role_id" onchange="this.form.submit()">
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected($selectedRole && (string) $selectedRole->id === (string) $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </form>

            @if ($selectedRole)
                <form method="POST" action="{{ route('master.role-permissions.store') }}" class="js-role-permission-form">
                    @csrf
                    <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">

                    <div class="erp-permission-toolbar">
                        <div>
                            <strong>{{ $selectedRole->name }}</strong>
                            <small class="text-muted d-block">Pilih global action dan akses lanjutan sesuai scope role.</small>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary js-permission-select-all" type="button" data-target=".js-permission-check">
                            <i class="bi bi-check2-square me-1"></i>Select All
                        </button>
                    </div>

                    <div class="erp-permission-block">
                        <div class="erp-permission-block-head">
                            <div>
                                <strong>Global Action Matrix</strong>
                                <small>Kolom hanya untuk action umum lintas module.</small>
                            </div>
                        </div>
                        <div class="erp-matrix-wrap">
                            <table class="table table-bordered align-middle erp-permission-matrix">
                                <thead>
                                    <tr>
                                        <th class="erp-matrix-module-col">Module</th>
                                        @foreach ($matrixActions as $action)
                                            <th>{{ $action->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modules as $module)
                                        <tr>
                                            <th>
                                                <span class="d-block">{{ $module->name }}</span>
                                                <small class="text-muted">{{ $module->slug }}</small>
                                            </th>
                                            @foreach ($matrixActions as $action)
                                                @php
                                                    $permission = $permissions->get($module->id.'|'.$action->id);
                                                @endphp
                                                <td class="text-center">
                                                    @if ($permission)
                                                        <input class="form-check-input js-permission-check js-module-{{ $module->id }}" data-module-target=".js-module-{{ $module->id }}" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked(in_array((string) $permission->id, $selected, true)) title="{{ $permission->permission_slug }}">
                                                    @else
                                                        <span class="text-muted" title="Generate permission first">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="erp-permission-block">
                        <div class="erp-permission-block-head">
                            <div>
                                <strong>Feature & Workflow Access</strong>
                                <small>Permission khusus module tidak menjadi kolom global.</small>
                            </div>
                        </div>

                        <div class="accordion erp-permission-accordion" id="rolePermissionModules">
                            @foreach ($modules as $module)
                                @php
                                    $advancedPermissions = $module->permissions
                                        ->reject(fn ($permission) => ($permission->permission_type ?? 'feature_access') === \App\Models\Master\SysPermission::TYPE_GLOBAL_ACTION)
                                        ->groupBy(fn ($permission) => $permission->permission_type ?? \App\Models\Master\SysPermission::TYPE_FEATURE_ACCESS);
                                    $modulePermissionIds = $module->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
                                    $moduleSelectedCount = collect($modulePermissionIds)->intersect($selected)->count();
                                    $collapseId = 'module-permission-'.$module->id;
                                @endphp

                                <div class="accordion-item erp-permission-module-card">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                            <span>
                                                <strong>{{ $module->name }}</strong>
                                                <small>{{ $module->slug }}</small>
                                            </span>
                                            <span class="erp-module-permission-count" data-module-target=".js-module-{{ $module->id }}">
                                                {{ $moduleSelectedCount }}/{{ count($modulePermissionIds) }}
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="{{ $collapseId }}" class="accordion-collapse collapse" data-bs-parent="#rolePermissionModules">
                                        <div class="accordion-body">
                                            <div class="erp-module-permission-actions">
                                                <button class="btn btn-sm btn-outline-secondary js-permission-select-all" type="button" data-target=".js-module-{{ $module->id }}">
                                                    <i class="bi bi-check2-square me-1"></i>Select Module
                                                </button>
                                            </div>

                                            @if ($advancedPermissions->isEmpty())
                                                <div class="text-muted py-2">Tidak ada permission khusus untuk module ini.</div>
                                            @endif

                                            @foreach ($sectionLabels as $type => $label)
                                                @php
                                                    $sectionPermissions = $advancedPermissions->get($type, collect());
                                                @endphp
                                                @if ($sectionPermissions->isNotEmpty())
                                                    <section class="erp-permission-section">
                                                        <div class="erp-permission-section-head">
                                                            <strong>{{ $label }}</strong>
                                                            <button class="btn btn-sm btn-link js-permission-select-all" type="button" data-target=".js-section-{{ $module->id }}-{{ $type }}">Select section</button>
                                                        </div>
                                                        <div class="erp-permission-checkgrid">
                                                            @foreach ($sectionPermissions as $permission)
                                                                <label class="erp-permission-check">
                                                                    <input class="form-check-input js-permission-check js-module-{{ $module->id }} js-section-{{ $module->id }}-{{ $type }}" data-module-target=".js-module-{{ $module->id }}" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked(in_array((string) $permission->id, $selected, true))>
                                                                    <span>
                                                                        <strong>{{ $permission->permission_name ?: \Illuminate\Support\Str::headline($permission->permission_slug ?? $permission->name) }}</strong>
                                                                        <small>{{ $permission->permission_slug ?? $permission->name }}</small>
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </section>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-save me-1"></i> Save Permissions</button>
                    </div>
                </form>
            @endif

         
        </div>
    </section>
@endsection
