<?php

use App\Models\Master\SysUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureAuthColumns();
        $this->ensureRolePermissionTable();
        $this->ensureUserRoleTable();
        $this->ensureUserPermissionTable();
        $this->backfillDefaultAccess();
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_user_permissions');
        Schema::dropIfExists('sys_user_roles');

        if (Schema::hasTable('sys_role_permissions') && ! Schema::hasTable('role_permissions')) {
            Schema::rename('sys_role_permissions', 'role_permissions');
        }
    }

    private function ensureAuthColumns(): void
    {
        Schema::table('sys_users', function (Blueprint $table) {
            if (! Schema::hasColumn('sys_users', 'remember_token')) {
                $table->rememberToken();
            }

            if (! Schema::hasColumn('sys_users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
        });

        Schema::table('sys_roles', function (Blueprint $table) {
            if (! Schema::hasColumn('sys_roles', 'guard_name')) {
                $table->string('guard_name', 50)->default('web')->index();
            }
        });

        Schema::table('sys_permissions', function (Blueprint $table) {
            if (! Schema::hasColumn('sys_permissions', 'guard_name')) {
                $table->string('guard_name', 50)->default('web')->index();
            }
        });

        DB::table('sys_roles')->whereNull('guard_name')->update(['guard_name' => 'web']);
        DB::table('sys_permissions')->whereNull('guard_name')->update(['guard_name' => 'web']);
    }

    private function ensureRolePermissionTable(): void
    {
        if (Schema::hasTable('role_permissions') && ! Schema::hasTable('sys_role_permissions')) {
            Schema::rename('role_permissions', 'sys_role_permissions');
        }

        if (Schema::hasTable('sys_role_permissions')) {
            return;
        }

        Schema::create('sys_role_permissions', function (Blueprint $table) {
            $this->foreignKeyColumn($table, 'role_id', 'sys_roles');
            $this->foreignKeyColumn($table, 'permission_id', 'sys_permissions');
            $table->primary(['permission_id', 'role_id'], 'sys_role_permissions_pk');
        });
    }

    private function ensureUserRoleTable(): void
    {
        if (Schema::hasTable('sys_user_roles')) {
            return;
        }

        Schema::create('sys_user_roles', function (Blueprint $table) {
            $this->foreignKeyColumn($table, 'role_id', 'sys_roles');
            $this->modelKeyColumn($table, 'model_id', 'sys_users');
            $table->string('model_type');
            $table->index(['model_id', 'model_type'], 'sys_user_roles_model_index');
            $table->primary(['role_id', 'model_id', 'model_type'], 'sys_user_roles_pk');
        });
    }

    private function ensureUserPermissionTable(): void
    {
        if (Schema::hasTable('sys_user_permissions')) {
            return;
        }

        Schema::create('sys_user_permissions', function (Blueprint $table) {
            $this->foreignKeyColumn($table, 'permission_id', 'sys_permissions');
            $this->modelKeyColumn($table, 'model_id', 'sys_users');
            $table->string('model_type');
            $table->index(['model_id', 'model_type'], 'sys_user_permissions_model_index');
            $table->primary(['permission_id', 'model_id', 'model_type'], 'sys_user_permissions_pk');
        });
    }

    private function backfillDefaultAccess(): void
    {
        $superAdmin = DB::table('sys_roles')->where('code', 'SUPERADMIN')->orWhere('name', 'Super Admin')->first();

        if (! $superAdmin) {
            $superAdminId = $this->newKey('sys_roles');
            DB::table('sys_roles')->insert([
                'id' => $superAdminId,
                'code' => 'SUPERADMIN',
                'name' => 'Super Admin',
                'guard_name' => 'web',
                'description' => 'Full system access',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $superAdmin = DB::table('sys_roles')->where('id', $superAdminId)->first();
        } else {
            DB::table('sys_roles')->where('id', $superAdmin->id)->update([
                'code' => 'SUPERADMIN',
                'name' => 'Super Admin',
                'guard_name' => 'web',
                'updated_at' => now(),
            ]);

            $superAdmin = DB::table('sys_roles')->where('id', $superAdmin->id)->first();
        }

        foreach (DB::table('sys_permissions')->pluck('id') as $permissionId) {
            DB::table('sys_role_permissions')->updateOrInsert([
                'role_id' => $superAdmin->id,
                'permission_id' => $permissionId,
            ]);
        }

        $adminUser = DB::table('sys_users')->where('username', 'admin')->orWhere('email', 'admin@zainerp.local')->first()
            ?? DB::table('sys_users')->orderBy('id')->first();

        if ($adminUser) {
            DB::table('sys_user_roles')->updateOrInsert([
                'role_id' => $superAdmin->id,
                'model_id' => $adminUser->id,
                'model_type' => SysUser::class,
            ]);
        }
    }

    private function foreignKeyColumn(Blueprint $table, string $column, string $referencedTable): void
    {
        if ($this->isUuidKey($referencedTable)) {
            $table->foreignUuid($column)->constrained($referencedTable)->cascadeOnDelete();

            return;
        }

        $table->unsignedBigInteger($column);
        $table->foreign($column)->references('id')->on($referencedTable)->cascadeOnDelete();
    }

    private function modelKeyColumn(Blueprint $table, string $column, string $referencedTable): void
    {
        if ($this->isUuidKey($referencedTable)) {
            $table->uuid($column);

            return;
        }

        $table->unsignedBigInteger($column);
    }

    private function isUuidKey(string $table): bool
    {
        return DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', $table)
            ->where('column_name', 'id')
            ->value('data_type') === 'uuid';
    }

    private function newKey(string $table): string|int
    {
        if ($this->isUuidKey($table)) {
            return (string) Str::uuid();
        }

        return ((int) DB::table($table)->max('id')) + 1;
    }
};
