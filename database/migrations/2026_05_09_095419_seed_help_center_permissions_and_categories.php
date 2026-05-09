<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sys_modules') || ! Schema::hasTable('sys_actions') || ! Schema::hasTable('sys_permissions')) {
            return;
        }

        $moduleId = DB::table('sys_modules')->where('slug', 'help')->value('id') ?: (string) Str::uuid();
        DB::table('sys_modules')->updateOrInsert(
            ['slug' => 'help'],
            ['id' => $moduleId, 'name' => 'Help Center', 'icon' => 'bi-life-preserver', 'is_active' => true, 'sort_no' => 60, 'updated_at' => now(), 'created_at' => now()]
        );

        $actions = ['view', 'create', 'edit', 'delete', 'publish'];
        foreach ($actions as $index => $action) {
            $actionId = DB::table('sys_actions')->where('slug', $action)->value('id') ?: (string) Str::uuid();
            DB::table('sys_actions')->updateOrInsert(
                ['slug' => $action],
                ['id' => $actionId, 'name' => Str::headline($action), 'is_active' => true, 'sort_no' => ($index + 1) * 10, 'updated_at' => now(), 'created_at' => now()]
            );

            $code = 'help.'.$action;
            DB::table('sys_permissions')->updateOrInsert(
                ['code' => $code],
                [
                    'module_id' => $moduleId,
                    'action_id' => $actionId,
                    'module' => 'help',
                    'name' => $code,
                    'permission_name' => Str::headline($code),
                    'permission_slug' => $code,
                    'guard_name' => 'web',
                    'description' => Str::headline($code),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        if (Schema::hasTable('sys_roles') && Schema::hasTable('sys_role_permissions')) {
            $superAdminId = DB::table('sys_roles')->where('name', 'Super Admin')->orWhere('code', 'SUPERADMIN')->value('id');

            if ($superAdminId) {
                foreach (DB::table('sys_permissions')->where('module', 'help')->pluck('id') as $permissionId) {
                    DB::table('sys_role_permissions')->updateOrInsert(
                        ['role_id' => $superAdminId, 'permission_id' => $permissionId],
                        ['updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        }

        $this->seedCategories();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('help_categories')) {
            DB::table('help_categories')->whereIn('type', ['USER_GUIDE', 'DEVELOPER_DOCS', 'FAQ', 'TROUBLESHOOTING'])->delete();
        }
    }

    private function seedCategories(): void
    {
        if (! Schema::hasTable('help_categories')) {
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
                DB::table('help_categories')->updateOrInsert(
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
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $sort += 10;
            }
        }
    }
};
