<?php

namespace Database\Seeders;

use App\Models\Master\RefJabatan;
use App\Models\Master\RefTicketCategory;
use App\Models\Master\RefTicketSla;
use App\Models\Master\RefUnit;
use App\Models\Master\SysAction;
use App\Models\Master\SysModule;
use App\Models\Master\SysPermission;
use App\Models\Master\SysRole;
use App\Models\Master\SysUser;
use App\Models\HelpCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class MasterDataSeeder extends Seeder
{
    private array $globalActions = ['view', 'create', 'update', 'edit', 'delete', 'approve', 'reject', 'assign', 'export', 'print', 'upload', 'publish', 'manage'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $restoreOrCreate = function (string $model, array $where, array $values = []) {
            $record = $model::withTrashed()->where($where)->first();

            if ($record) {
                $record->restore();
                $record->fill($values)->save();

                return $record;
            }

            return $model::create($where + $values);
        };

        $unit = $restoreOrCreate(RefUnit::class, ['code' => 'HO'], ['name' => 'Head Office', 'is_active' => true]);

        $jabatan = $restoreOrCreate(RefJabatan::class, ['code' => 'ADM'], ['name' => 'Administrator', 'level' => 1, 'is_active' => true]);

        $adminUser = $restoreOrCreate(SysUser::class,
            ['username' => 'admin'],
            [
                'unit_id' => $unit->id,
                'jabatan_id' => $jabatan->id,
                'employee_no' => 'EMP-0001',
                'name' => 'Administrator',
                'email' => 'admin@zainerp.local',
                'password' => 'password',
                'is_active' => true,
            ]
        );

        $superAdmin = $restoreOrCreate(SysRole::class, ['code' => 'SUPERADMIN'], ['name' => 'Super Admin', 'guard_name' => 'web', 'is_active' => true]);
        $admin = $restoreOrCreate(SysRole::class, ['code' => 'ADMIN'], ['name' => 'Admin', 'guard_name' => 'web', 'is_active' => true]);
        $restoreOrCreate(SysRole::class, ['code' => 'USER'], ['name' => 'User', 'guard_name' => 'web', 'is_active' => true]);

        $moduleRecords = collect();
        $actionRecords = collect();

        if (Schema::hasTable('sys_modules') && Schema::hasTable('sys_actions')) {
            foreach ([
                ['Users', 'users', 'bi-people', 10],
                ['Roles', 'roles', 'bi-shield-check', 20],
                ['Permissions', 'permissions', 'bi-key', 30],
                ['Ticket', 'ticket', 'bi-ticket-detailed', 50],
                ['Help Center', 'help', 'bi-life-preserver', 60],
                ['Role Permissions', 'role-permissions', 'bi-diagram-2', 70],
                ['Settings', 'settings', 'bi-gear', 80],
            ] as [$name, $slug, $icon, $sortNo]) {
                $moduleRecords[$slug] = $restoreOrCreate(SysModule::class, ['slug' => $slug], ['name' => $name, 'icon' => $icon, 'sort_no' => $sortNo, 'is_active' => true]);
            }

            foreach ($this->globalActions as $index => $slug) {
                $actionRecords[$slug] = $restoreOrCreate(SysAction::class, ['slug' => $slug], ['name' => Str::headline($slug), 'sort_no' => ($index + 1) * 10, 'is_active' => true]);
            }
        }

        $permissions = [
            'users' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'update', 'delete'],
            'permissions' => ['view', 'create', 'update', 'delete', 'manage'],
            'role-permissions' => ['manage'],
            'tickets' => ['view', 'create', 'update', 'delete', 'assign', 'approve'],
            'help' => ['view', 'create', 'edit', 'delete', 'publish'],
            'settings' => ['view', 'update'],
        ];

        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action) {
                $slug = $module.'.'.$action;
                $permissionModule = $module === 'tickets' ? 'ticket' : $module;
                $moduleRecord = $moduleRecords->get($permissionModule);
                $actionRecord = $actionRecords->get($action);
                $restoreOrCreate(SysPermission::class,
                    ['code' => $slug],
                    [
                        'module_id' => $moduleRecord?->id,
                        'action_id' => $actionRecord?->id,
                        'module' => $permissionModule,
                        'name' => $slug,
                        'permission_name' => Str::headline($slug),
                        'permission_slug' => $slug,
                        'guard_name' => 'web',
                        'description' => Str::headline($slug),
                    ]
                );
            }
        }

        $superAdmin->permissions()->sync(SysPermission::pluck('id')->all());
        $admin->permissions()->sync(SysPermission::whereIn('module', ['users', 'tickets', 'ticket', 'help', 'settings'])->pluck('id')->all());
        $adminUser->assignRole($superAdmin);

        $this->seedHelpCategories($restoreOrCreate);

        foreach (['low' => [120, 2880], 'medium' => [60, 1440], 'high' => [30, 480], 'critical' => [15, 240]] as $priority => [$response, $resolve]) {
            $restoreOrCreate(RefTicketSla::class,
                ['name' => ucfirst($priority).' SLA'],
                ['priority' => $priority, 'response_minutes' => $response, 'resolve_minutes' => $resolve]
            );
        }

        $restoreOrCreate(RefTicketCategory::class,
            ['code' => 'GENERAL'],
            ['name' => 'General Support', 'sla_id' => RefTicketSla::where('priority', 'medium')->value('id'), 'is_active' => true, 'sort_no' => 1]
        );

        foreach ([
            'app_name' => ['SupportDesk Pro', 'string', 'Application display name'],
            'company_name' => ['Zain ERP', 'string', 'Company name'],
            'default_ticket_sla' => ['medium', 'string', 'Default ticket SLA priority'],
            'allow_attachment' => ['1', 'boolean', 'Enable ticket attachment upload'],
            'max_upload_size' => ['10240', 'integer', 'Maximum upload size in kilobytes'],
        ] as $key => [$value, $type, $description]) {
            Setting::firstOrCreate(['key' => $key], compact('value', 'type', 'description'));
        }
    }

    private function seedHelpCategories(callable $restoreOrCreate): void
    {
        if (! class_exists(HelpCategory::class) || ! Schema::hasTable('help_categories')) {
            return;
        }

        $categories = [
            'USER_GUIDE' => ['Getting Started', 'Dashboard', 'Ticket Management', 'Notifications', 'Settings'],
            'DEVELOPER_DOCS' => ['Installation', 'Configuration', 'Environment Setup', 'Queue', 'Storage', 'API Documentation', 'Deployment'],
            'FAQ' => ['General Questions', 'Account', 'Ticketing', 'Troubleshooting'],
            'TROUBLESHOOTING' => ['Login Issue', 'Upload Error', 'Notification Problem', 'Permission Error'],
        ];

        $sort = 10;
        foreach ($categories as $type => $names) {
            foreach ($names as $name) {
                $restoreOrCreate(HelpCategory::class,
                    ['slug' => Str::slug($type.' '.$name)],
                    [
                        'name' => $name,
                        'type' => $type,
                        'icon' => match ($type) {
                            'DEVELOPER_DOCS' => 'bi-code-square',
                            'FAQ' => 'bi-question-circle',
                            'TROUBLESHOOTING' => 'bi-tools',
                            default => 'bi-book',
                        },
                        'sort_no' => $sort,
                        'is_active' => true,
                    ]
                );
                $sort += 10;
            }
        }
    }
}
