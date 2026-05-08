<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_modules')) {
            Schema::create('sys_modules', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name', 150);
                $table->string('slug', 100)->unique();
                $table->string('icon', 80)->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_no')->default(0)->index();
                $table->timestamps();
                $table->softDeletes();
                $this->auditColumns($table);
            });
        }

        if (! Schema::hasTable('sys_actions')) {
            Schema::create('sys_actions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name', 100);
                $table->string('slug', 80)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_no')->default(0)->index();
                $table->timestamps();
                $table->softDeletes();
                $this->auditColumns($table);
            });
        }

        Schema::table('sys_permissions', function (Blueprint $table) {
            if (! Schema::hasColumn('sys_permissions', 'module_id')) {
                $table->uuid('module_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('sys_permissions', 'action_id')) {
                $table->uuid('action_id')->nullable()->after('module_id');
            }

            if (! Schema::hasColumn('sys_permissions', 'permission_name')) {
                $table->string('permission_name', 180)->nullable()->after('name');
            }

            if (! Schema::hasColumn('sys_permissions', 'permission_slug')) {
                $table->string('permission_slug', 180)->nullable()->after('permission_name');
            }
        });

        $this->seedModulesAndActions();
        $this->backfillPermissions();

        Schema::table('sys_permissions', function (Blueprint $table) {
            if (! $this->hasForeignKey('sys_permissions', 'sys_permissions_module_id_foreign')) {
                $table->foreign('module_id')->references('id')->on('sys_modules')->nullOnDelete();
            }

            if (! $this->hasForeignKey('sys_permissions', 'sys_permissions_action_id_foreign')) {
                $table->foreign('action_id')->references('id')->on('sys_actions')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sys_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('sys_permissions', 'module_id')) {
                $table->dropForeign(['module_id']);
            }

            if (Schema::hasColumn('sys_permissions', 'action_id')) {
                $table->dropForeign(['action_id']);
            }

            $table->dropColumn(array_values(array_filter([
                Schema::hasColumn('sys_permissions', 'module_id') ? 'module_id' : null,
                Schema::hasColumn('sys_permissions', 'action_id') ? 'action_id' : null,
                Schema::hasColumn('sys_permissions', 'permission_name') ? 'permission_name' : null,
                Schema::hasColumn('sys_permissions', 'permission_slug') ? 'permission_slug' : null,
            ])));
        });

        Schema::dropIfExists('sys_actions');
        Schema::dropIfExists('sys_modules');
    }

    private function auditColumns(Blueprint $table): void
    {
        $table->unsignedBigInteger('created_by')->nullable()->index();
        $table->unsignedBigInteger('updated_by')->nullable()->index();
        $table->unsignedBigInteger('deleted_by')->nullable()->index();
    }

    private function seedModulesAndActions(): void
    {
        foreach ([
            ['Users', 'users', 'bi-people', 10],
            ['Roles', 'roles', 'bi-shield-check', 20],
            ['Permissions', 'permissions', 'bi-key', 30],
            ['Ticket', 'ticket', 'bi-ticket-detailed', 50],
        ] as [$name, $slug, $icon, $sort]) {
            DB::table('sys_modules')->updateOrInsert(
                ['slug' => $slug],
                ['id' => DB::table('sys_modules')->where('slug', $slug)->value('id') ?: (string) Str::uuid(), 'name' => $name, 'icon' => $icon, 'is_active' => true, 'sort_no' => $sort, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        foreach (['view', 'create', 'update', 'delete', 'approve', 'reject', 'assign', 'export', 'print', 'upload'] as $index => $slug) {
            DB::table('sys_actions')->updateOrInsert(
                ['slug' => $slug],
                ['id' => DB::table('sys_actions')->where('slug', $slug)->value('id') ?: (string) Str::uuid(), 'name' => Str::headline($slug), 'is_active' => true, 'sort_no' => ($index + 1) * 10, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function backfillPermissions(): void
    {
        foreach (DB::table('sys_permissions')->get() as $permission) {
            $slug = $permission->permission_slug ?: ($permission->code ?: $permission->name);
            $slug = Str::of($slug)->lower()->replace(' ', '.')->toString();
            $parts = explode('.', $slug);
            $moduleSlug = $parts[0] ?? 'system';
            $actionSlug = $parts[count($parts) - 1] ?? 'view';

            $moduleId = DB::table('sys_modules')->where('slug', $moduleSlug)->value('id')
                ?: $this->createModuleFromSlug($moduleSlug, $permission->module ?? null);
            $actionId = DB::table('sys_actions')->where('slug', $actionSlug)->value('id')
                ?: $this->createActionFromSlug($actionSlug);

            DB::table('sys_permissions')->where('id', $permission->id)->update([
                'module_id' => $moduleId,
                'action_id' => $actionId,
                'module' => $moduleSlug,
                'code' => $slug,
                'name' => $slug,
                'permission_name' => $permission->permission_name ?: Str::headline($slug),
                'permission_slug' => $slug,
                'guard_name' => 'web',
            ]);
        }
    }

    private function createModuleFromSlug(string $slug, ?string $fallbackName): string
    {
        $id = (string) Str::uuid();

        DB::table('sys_modules')->insert([
            'id' => $id,
            'name' => $fallbackName ?: Str::headline($slug),
            'slug' => $slug,
            'is_active' => true,
            'sort_no' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createActionFromSlug(string $slug): string
    {
        $id = (string) Str::uuid();

        DB::table('sys_actions')->insert([
            'id' => $id,
            'name' => Str::headline($slug),
            'slug' => $slug,
            'is_active' => true,
            'sort_no' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function hasForeignKey(string $table, string $name): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('table_schema', 'public')
            ->where('table_name', $table)
            ->where('constraint_name', $name)
            ->exists();
    }
};
