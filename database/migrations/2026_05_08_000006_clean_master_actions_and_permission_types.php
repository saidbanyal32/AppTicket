<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $globalActions = ['view', 'create', 'update', 'delete', 'approve', 'reject', 'export', 'print', 'upload'];

    public function up(): void
    {
        if (! Schema::hasTable('sys_permissions')) {
            return;
        }

        DB::table('sys_permissions')
            ->where('permission_type', 'workflow_access')
            ->update(['permission_type' => 'workflow_tab', 'updated_at' => now()]);

        if (Schema::hasTable('sys_actions')) {
            $nonGlobalActionIds = DB::table('sys_actions')
                ->whereNotIn('slug', $this->globalActions)
                ->pluck('id');

            if ($nonGlobalActionIds->isNotEmpty()) {
                DB::table('sys_permissions')
                    ->whereIn('action_id', $nonGlobalActionIds)
                    ->update([
                        'action_id' => null,
                        'permission_type' => DB::raw("case when coalesce(permission_slug, code, name) like '%.tab.%' then 'workflow_tab' when coalesce(permission_slug, code, name) like '%.workflow.%' then 'workflow_tab' when coalesce(permission_slug, code, name) like '%analytics%' then 'advanced_access' when coalesce(permission_slug, code, name) like '%sla%' then 'advanced_access' when coalesce(permission_slug, code, name) like '%.manage' then 'advanced_access' else 'feature_access' end"),
                        'updated_at' => now(),
                    ]);

                $updates = ['is_active' => false, 'updated_at' => now()];

                if (Schema::hasColumn('sys_actions', 'deleted_at')) {
                    $updates['deleted_at'] = now();
                }

                DB::table('sys_actions')
                    ->whereIn('id', $nonGlobalActionIds)
                    ->update($updates);
            }

            $globalUpdates = ['is_active' => true, 'updated_at' => now()];

            if (Schema::hasColumn('sys_actions', 'deleted_at')) {
                $globalUpdates['deleted_at'] = null;
            }

            DB::table('sys_actions')
                ->whereIn('slug', $this->globalActions)
                ->update($globalUpdates);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sys_actions')) {
            $updates = ['is_active' => true, 'updated_at' => now()];

            if (Schema::hasColumn('sys_actions', 'deleted_at')) {
                $updates['deleted_at'] = null;
            }

            DB::table('sys_actions')
                ->whereIn('slug', ['assign', 'manage', 'adjust', 'my_request', 'need_assignment', 'assign_to_me', 'overdue', 'closed', 'all'])
                ->update($updates);
        }

        if (Schema::hasTable('sys_permissions')) {
            DB::table('sys_permissions')
                ->where('permission_type', 'workflow_tab')
                ->update(['permission_type' => 'workflow_access', 'updated_at' => now()]);
        }
    }
};
