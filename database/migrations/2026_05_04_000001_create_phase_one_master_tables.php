<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_units', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignUuid('parent_id')->nullable()->constrained('ref_units')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('ref_jabatan', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->integer('level')->default(1)->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('sys_users', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignUuid('unit_id')->constrained('ref_units')->restrictOnDelete();
            $table->foreignUuid('jabatan_id')->constrained('ref_jabatan')->restrictOnDelete();
            $table->string('employee_no', 50)->unique();
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('username', 80)->unique();
            $table->string('password');
            $table->string('phone', 50)->nullable();
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('sys_roles', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('guard_name', 50)->default('web')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('sys_permissions', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('module', 100)->index();
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->string('guard_name', 50)->default('web')->index();
            $table->text('description')->nullable();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('sys_role_permissions', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignUuid('role_id')->constrained('sys_roles')->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained('sys_permissions')->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('sys_user_roles', function (Blueprint $table) {
            $table->foreignUuid('role_id')->constrained('sys_roles')->cascadeOnDelete();
            $table->uuid('model_id');
            $table->string('model_type');
            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('sys_user_permissions', function (Blueprint $table) {
            $table->foreignUuid('permission_id')->constrained('sys_permissions')->cascadeOnDelete();
            $table->uuid('model_id');
            $table->string('model_type');
            $table->index(['model_id', 'model_type']);
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        foreach ($this->auditedTables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('sys_users')->nullOnDelete();
                $table->foreign('updated_by')->references('id')->on('sys_users')->nullOnDelete();
                $table->foreign('deleted_by')->references('id')->on('sys_users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sys_user_permissions');
        Schema::dropIfExists('sys_user_roles');
        Schema::dropIfExists('sys_role_permissions');
        Schema::dropIfExists('sys_permissions');
        Schema::dropIfExists('sys_roles');
        Schema::dropIfExists('sys_users');
        Schema::dropIfExists('ref_jabatan');
        Schema::dropIfExists('ref_units');
        Schema::enableForeignKeyConstraints();
    }

    private function masterColumns(Blueprint $table): void
    {
        $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
        $table->timestamps();
        $table->softDeletes();
        $table->uuid('created_by')->nullable()->index();
        $table->uuid('updated_by')->nullable()->index();
        $table->uuid('deleted_by')->nullable()->index();
    }

    private function auditedTables(): array
    {
        return [
            'ref_units',
            'ref_jabatan',
            'sys_users',
            'sys_roles',
            'sys_permissions',
            'sys_role_permissions',
        ];
    }
};
