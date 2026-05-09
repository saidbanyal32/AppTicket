<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $workflowPermissionSlugs = [
        'ticket.tab.my_request',
        'ticket.tab.need_assignment',
        'ticket.tab.assign_to_me',
        'ticket.tab.overdue',
        'ticket.tab.closed',
        'ticket.tab.all',
    ];

    private array $workflowActionSlugs = [
        'my_request',
        'need_assignment',
        'assign_to_me',
        'overdue',
        'closed',
        'all',
    ];

    private array $standardActionSlugs = ['view', 'create', 'update', 'delete', 'approve', 'reject', 'assign', 'export', 'print', 'upload', 'manage'];

    public function up(): void
    {
        if (Schema::hasTable('sys_permissions')) {
            $permissionIds = DB::table('sys_permissions')
                ->whereIn('code', $this->workflowPermissionSlugs)
                ->orWhereIn('name', $this->workflowPermissionSlugs)
                ->orWhereIn('permission_slug', $this->workflowPermissionSlugs)
                ->pluck('id');

            if ($permissionIds->isNotEmpty() && Schema::hasTable('sys_role_permissions')) {
                DB::table('sys_role_permissions')->whereIn('permission_id', $permissionIds)->delete();
            }

            if ($permissionIds->isNotEmpty() && Schema::hasTable('sys_user_permissions')) {
                DB::table('sys_user_permissions')->whereIn('permission_id', $permissionIds)->delete();
            }

            DB::table('sys_permissions')
                ->whereIn('id', $permissionIds)
                ->delete();

            if (Schema::hasColumn('sys_permissions', 'permission_type')) {
                Schema::table('sys_permissions', function (Blueprint $table) {
                    $table->dropColumn('permission_type');
                });
            }
        }

        if (Schema::hasTable('sys_actions')) {
            DB::table('sys_actions')
                ->whereIn('slug', $this->workflowActionSlugs)
                ->delete();

            $this->ensureStandardActions();
            $this->backfillStandardActionIds();
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sys_permissions') && ! Schema::hasColumn('sys_permissions', 'permission_type')) {
            Schema::table('sys_permissions', function (Blueprint $table) {
                $table->string('permission_type', 40)->default('global_action')->after('permission_slug')->index();
            });
        }
    }

    private function ensureStandardActions(): void
    {
        foreach ($this->standardActionSlugs as $index => $slug) {
            $values = [
                'name' => Str::headline($slug),
                'is_active' => true,
                'sort_no' => ($index + 1) * 10,
                'updated_at' => now(),
            ];

            if (! DB::table('sys_actions')->where('slug', $slug)->exists()) {
                $values['id'] = $this->newKey('sys_actions');
                $values['created_at'] = now();
            }

            DB::table('sys_actions')->updateOrInsert(['slug' => $slug], $values);
        }
    }

    private function backfillStandardActionIds(): void
    {
        if (! Schema::hasTable('sys_permissions')) {
            return;
        }

        $actionIds = DB::table('sys_actions')
            ->whereIn('slug', $this->standardActionSlugs)
            ->pluck('id', 'slug');

        foreach (DB::table('sys_permissions')->get(['id', 'code', 'name', 'permission_slug']) as $permission) {
            $slug = (string) ($permission->permission_slug ?: ($permission->code ?: $permission->name));
            $actionSlug = Str::afterLast($slug, '.');
            $actionId = $actionIds->get($actionSlug);

            if ($actionId) {
                DB::table('sys_permissions')->where('id', $permission->id)->update([
                    'action_id' => $actionId,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function newKey(string $table): string|int
    {
        return $this->usesUuidPrimaryKey($table)
            ? (string) Str::uuid()
            : ((int) DB::table($table)->max('id')) + 1;
    }

    private function usesUuidPrimaryKey(string $table): bool
    {
        return DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', $table)
            ->where('column_name', 'id')
            ->value('data_type') === 'uuid';
    }
};
