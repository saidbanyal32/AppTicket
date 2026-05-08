<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $moduleSlugs = [
        'item-categories',
        'item-units',
        'items',
        'inventory',
        'procurement',
        'projects',
        'project-sites',
        'cost-codes',
        'vendor-types',
        'vendors',
    ];

    private array $permissionPrefixes = [
        'item.',
        'items.',
        'material.',
        'materials.',
        'inventory.',
        'procurement.',
        'project.',
        'projects.',
        'project-sites.',
        'cost-codes.',
        'vendor.',
        'vendors.',
        'vendor-types.',
        'item-categories.',
        'item-units.',
    ];

    public function up(): void
    {
        $this->cleanupPermissions();
        $this->cleanupModules();
        $this->dropUnusedReferenceTables();
    }

    public function down(): void
    {
        // Intentionally no-op. Historical migrations still describe the old
        // tables; this cleanup migration removes retired modules from live apps.
    }

    private function cleanupPermissions(): void
    {
        if (! Schema::hasTable('sys_permissions')) {
            return;
        }

        $permissionIds = DB::table('sys_permissions')
            ->where(function ($query) {
                foreach (['code', 'name', 'permission_slug'] as $column) {
                    if (! Schema::hasColumn('sys_permissions', $column)) {
                        continue;
                    }

                    $query->orWhereIn($column, [
                        'inventory.stock.adjust',
                    ]);

                    foreach ($this->permissionPrefixes as $prefix) {
                        $query->orWhere($column, 'like', $prefix.'%');
                    }
                }

                if (Schema::hasColumn('sys_permissions', 'module')) {
                    $query->orWhereIn('module', $this->moduleSlugs)
                        ->orWhereIn('module', ['item', 'material', 'project', 'vendor']);
                }
            })
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('sys_role_permissions')) {
            DB::table('sys_role_permissions')->whereIn('permission_id', $permissionIds)->delete();
        }

        if (Schema::hasTable('sys_user_permissions')) {
            DB::table('sys_user_permissions')->whereIn('permission_id', $permissionIds)->delete();
        }

        DB::table('sys_permissions')->whereIn('id', $permissionIds)->delete();
    }

    private function cleanupModules(): void
    {
        if (! Schema::hasTable('sys_modules')) {
            return;
        }

        DB::table('sys_modules')
            ->whereIn('slug', $this->moduleSlugs)
            ->delete();
    }

    private function dropUnusedReferenceTables(): void
    {
        Schema::dropIfExists('ref_vendor');
        Schema::dropIfExists('ref_vendor_type');
        Schema::dropIfExists('ref_cost_codes');
        Schema::dropIfExists('ref_project_sites');
        Schema::dropIfExists('ref_project');
        Schema::dropIfExists('ref_items');
        Schema::dropIfExists('ref_item_unit');
        Schema::dropIfExists('ref_item_categories');
    }
};
