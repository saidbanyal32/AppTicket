<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\SysAction;
use App\Models\Master\SysModule;
use App\Models\Master\SysPermission;
use App\Models\Master\SysRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends BaseMasterController
{
    protected string $resourceKey = 'role-permissions';

    private array $globalActionSlugs = ['view', 'create', 'update', 'delete', 'approve', 'reject', 'export', 'print'];

    public function index(): View
    {
        $this->authorizeMasterAction('view');

        $roles = SysRole::query()->orderBy('name')->get();
        $selectedRole = $roles->firstWhere('id', request('role_id')) ?? $roles->first();
        $selected = $selectedRole
            ? $selectedRole->permissions()->pluck('sys_permissions.id')->map(fn ($id) => (string) $id)->all()
            : [];
        $modules = SysModule::query()
            ->where('is_active', true)
            ->with(['permissions' => fn ($query) => $query->with('action')->orderBy('permission_name')->orderBy('name')])
            ->orderBy('sort_no')
            ->orderBy('name')
            ->get();
        $globalActions = SysAction::query()
            ->where('is_active', true)
            ->whereIn('slug', $this->globalActionSlugs)
            ->orderBy('sort_no')
            ->orderBy('name')
            ->get();
        $globalPermissions = SysPermission::query()
            ->where('permission_type', SysPermission::TYPE_GLOBAL_ACTION)
            ->get()
            ->keyBy(fn (SysPermission $permission) => $permission->module_id.'|'.$permission->action_id);
        $sectionLabels = [
            SysPermission::TYPE_WORKFLOW_ACCESS => 'Workflow Access',
            SysPermission::TYPE_FEATURE_ACCESS => 'Feature Access',
            SysPermission::TYPE_ADVANCED_ACCESS => 'Advanced Access',
        ];

        return view('access.role-permissions.matrix', $this->viewData([
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'modules' => $modules,
            'matrixActions' => $globalActions,
            'permissions' => $globalPermissions,
            'sectionLabels' => $sectionLabels,
            'selected' => $selected,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeMasterAction('update');

        $data = $request->validate([
            'role_id' => ['required', 'exists:sys_roles,id'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:sys_permissions,id'],
        ]);

        $role = SysRole::query()->findOrFail($data['role_id']);
        $role->permissions()->sync($data['permission_ids'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('master.role-permissions.index', ['role_id' => $role->id])
            ->with('status', 'Permission matrix untuk role '.$role->name.' berhasil disimpan.');
    }
}
