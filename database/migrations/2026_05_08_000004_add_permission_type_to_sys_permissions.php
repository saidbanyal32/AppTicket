<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $globalActions = ['view', 'create', 'update', 'delete', 'approve', 'reject', 'export', 'print'];

    public function up(): void
    {
        if (! Schema::hasTable('sys_permissions')) {
            return;
        }

        Schema::table('sys_permissions', function (Blueprint $table) {
            if (! Schema::hasColumn('sys_permissions', 'permission_type')) {
                $table->string('permission_type', 40)->default('feature_access')->after('permission_slug')->index();
            }
        });

        $this->backfillPermissionTypes();
    }

    public function down(): void
    {
        Schema::table('sys_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('sys_permissions', 'permission_type')) {
                $table->dropColumn('permission_type');
            }
        });
    }

    private function backfillPermissionTypes(): void
    {
        $actions = Schema::hasTable('sys_actions')
            ? DB::table('sys_actions')->pluck('slug', 'id')
            : collect();

        foreach (DB::table('sys_permissions')->get() as $permission) {
            $slug = (string) ($permission->permission_slug ?: ($permission->code ?: $permission->name));
            $actionSlug = $permission->action_id ? $actions->get($permission->action_id) : Str::afterLast($slug, '.');

            DB::table('sys_permissions')->where('id', $permission->id)->update([
                'permission_type' => $this->classify($slug, (string) $actionSlug),
            ]);
        }
    }

    private function classify(string $slug, string $actionSlug): string
    {
        if (in_array($actionSlug, $this->globalActions, true)) {
            return 'global_action';
        }

        if (Str::contains($slug, ['.tab.', '.workflow.']) || in_array($actionSlug, [
            'my_request',
            'need_assignment',
            'assign_to_me',
            'overdue',
            'closed',
            'dashboard',
            'monitoring',
        ], true)) {
            return 'workflow_tab';
        }

        if (Str::contains($slug, ['analytics', 'sla', 'monitor', 'adjust', 'opname', 'transfer', 'manage'])) {
            return 'advanced_access';
        }

        return 'feature_access';
    }
};
