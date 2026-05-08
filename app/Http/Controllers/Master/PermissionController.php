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

    private array $globalActions = ['view', 'create', 'update', 'delete', 'approve', 'reject', 'export', 'print', 'upload'];

    private array $workflowTabs = [
        'ticket' => [
            'my_request' => 'My Request',
            'need_assignment' => 'Need Assignment',
            'assign_to_me' => 'Assign To Me',
            'overdue' => 'Overdue',
            'closed' => 'Closed',
            'all' => 'All Tickets',
        ],
    ];

    private array $featureAccess = [
        'ticket' => [
            'assign' => 'Assign Ticket',
        ],
    ];

    private array $advancedAccess = [
        'ticket' => [
            'analytics.view' => 'Ticket Analytics',
            'sla.monitor' => 'SLA Monitoring',
        ],
    ];

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
            'workflowTabs' => $this->workflowTabs,
            'featureAccess' => $this->featureAccess,
            'advancedAccess' => $this->advancedAccess,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeMasterAction('create');

        $data = $request->validate([
            'module_id' => ['required', 'exists:sys_modules,id'],
            'action_ids' => ['nullable', 'array'],
            'action_ids.*' => ['exists:sys_actions,id'],
            'workflow_tabs' => ['nullable', 'array'],
            'workflow_tabs.*' => ['string', 'max:120'],
            'feature_access' => ['nullable', 'array'],
            'feature_access.*' => ['string', 'max:120'],
            'advanced_access' => ['nullable', 'array'],
            'advanced_access.*' => ['string', 'max:120'],
        ]);

        if (empty($data['action_ids']) && empty($data['workflow_tabs']) && empty($data['feature_access']) && empty($data['advanced_access'])) {
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
            $slug = $module->slug.'.'.$action->slug;
            $created += $this->createOrUpdatePermission($slug, [
                'module_id' => $module->id,
                'action_id' => $action->id,
                'module' => $module->slug,
                'permission_name' => Str::headline($module->name.' '.$action->name),
                'permission_type' => SysPermission::TYPE_GLOBAL_ACTION,
            ]);
        }

        $created += $this->generateCustomPermissions($module, $data['workflow_tabs'] ?? [], $this->workflowTabs[$module->slug] ?? [], 'tab', SysPermission::TYPE_WORKFLOW_ACCESS);
        $created += $this->generateCustomPermissions($module, $data['feature_access'] ?? [], $this->featureAccess[$module->slug] ?? [], null, SysPermission::TYPE_FEATURE_ACCESS);
        $created += $this->generateCustomPermissions($module, $data['advanced_access'] ?? [], $this->advancedAccess[$module->slug] ?? [], null, SysPermission::TYPE_ADVANCED_ACCESS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('master.permissions.index')
            ->with('status', $created.' permission baru dibuat. Permission existing diperbarui otomatis.');
    }

    private function permissionTypeForAction(string $actionSlug): string
    {
        return in_array($actionSlug, $this->globalActions, true)
            ? SysPermission::TYPE_GLOBAL_ACTION
            : SysPermission::TYPE_FEATURE_ACCESS;
    }

    private function generateCustomPermissions(SysModule $module, array $requested, array $catalog, ?string $namespace, string $type): int
    {
        $created = 0;

        foreach (array_intersect($requested, array_keys($catalog)) as $key) {
            $slug = $namespace ? $module->slug.'.'.$namespace.'.'.$key : $module->slug.'.'.$key;
            $created += $this->createOrUpdatePermission($slug, [
                'module_id' => $module->id,
                'action_id' => null,
                'module' => $module->slug,
                'permission_name' => $catalog[$key],
                'permission_type' => $type,
            ]);
        }

        return $created;
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
