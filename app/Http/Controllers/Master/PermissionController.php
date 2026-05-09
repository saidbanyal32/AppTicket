<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\SysAction;
use App\Models\Master\SysModule;
use App\Models\Master\SysPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends BaseMasterController
{
    protected string $resourceKey = 'permissions';

    private array $globalActions = ['view', 'create', 'update', 'delete', 'approve', 'reject', 'assign', 'export', 'print', 'upload', 'manage'];

    public function index(): View
    {
        $this->authorizeMasterAction('view');

        return view('access.permissions.index', $this->viewData([
            'modules' => SysModule::query()
                ->with(['permissions.action'])
                ->orderBy('sort_no')
                ->orderBy('name')
                ->get(),
            'actions' => SysAction::query()
                ->orderBy('sort_no')
                ->orderBy('name')
                ->get(),
        ]));
    }

    public function create(): View
    {
        $this->authorizeMasterAction('create');

        return view('access.permissions.generator', $this->viewData([
            'modules' => SysModule::query()->where('is_active', true)->orderBy('sort_no')->orderBy('name')->get(),
            'permissionActions' => SysAction::query()
                ->where('is_active', true)
                ->whereIn('slug', $this->globalActions)
                ->orderBy('sort_no')
                ->orderBy('name')
                ->get(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeMasterAction('create');

        $data = $request->validate([
            'module_id' => ['required', 'exists:sys_modules,id'],
            'action_ids' => ['nullable', 'array'],
            'action_ids.*' => ['exists:sys_actions,id'],
        ]);

        if (empty($data['action_ids'])) {
            return back()
                ->withInput()
                ->withErrors(['action_ids' => 'Pilih minimal satu permission untuk dibuat.']);
        }

        $module = SysModule::query()->findOrFail($data['module_id']);
        $actions = SysAction::query()
            ->whereIn('id', $data['action_ids'] ?? [])
            ->whereIn('slug', $this->globalActions)
            ->get();
        $created = 0;

        foreach ($actions as $action) {
            $permissionResource = $this->permissionResourceSlug($module->slug);
            $slug = $permissionResource.'.'.$action->slug;
            $created += $this->createOrUpdatePermission($slug, [
                'module_id' => $module->id,
                'action_id' => $action->id,
                'module' => $module->slug,
                'permission_name' => Str::headline($module->name.' '.$action->name),
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('master.permissions.index')
            ->with('status', $created.' permission baru dibuat. Permission existing diperbarui otomatis.');
    }

    private function permissionResourceSlug(string $moduleSlug): string
    {
        return $moduleSlug === 'ticket' ? 'tickets' : $moduleSlug;
    }

    private function createOrUpdatePermission(string $slug, array $attributes): int
    {
        $permission = SysPermission::query()->withTrashed()->firstOrNew(['permission_slug' => $slug]);

        if ($permission->trashed()) {
            $permission->restore();
        }

        $wasNew = ! $permission->exists;
        $permission->fill($attributes + [
            'code' => $slug,
            'name' => $slug,
            'permission_slug' => $slug,
            'guard_name' => 'web',
        ])->save();

        return $wasNew ? 1 : 0;
    }
}
