<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_units', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignId('parent_id')->nullable()->constrained('ref_units')->nullOnDelete();
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
            $table->foreignId('unit_id')->constrained('ref_units')->restrictOnDelete();
            $table->foreignId('jabatan_id')->constrained('ref_jabatan')->restrictOnDelete();
            $table->string('employee_no', 50)->unique();
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('username', 80)->unique();
            $table->string('password');
            $table->string('phone', 50)->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('sys_roles', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('sys_permissions', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('module', 100)->index();
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignId('role_id')->constrained('sys_roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('sys_permissions')->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('ref_item_categories', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignId('parent_id')->nullable()->constrained('ref_item_categories')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('ref_item_unit', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
        });

        Schema::create('ref_items', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignId('category_id')->constrained('ref_item_categories')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('ref_item_unit')->restrictOnDelete();
            $table->string('item_code', 50)->unique();
            $table->string('item_name', 180);
            $table->text('specification')->nullable();
            $table->decimal('minimum_stock', 18, 4)->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('ref_project', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('project_code', 50)->unique();
            $table->string('project_name', 180);
            $table->string('owner_name', 180)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('contract_value', 20, 2)->nullable();
            $table->string('status', 50)->default('planning')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('ref_project_sites', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignId('project_id')->constrained('ref_project')->cascadeOnDelete();
            $table->string('site_code', 50);
            $table->string('site_name', 180);
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unique(['project_id', 'site_code']);
        });

        Schema::create('ref_cost_codes', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignId('parent_id')->nullable()->constrained('ref_cost_codes')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('ref_project')->cascadeOnDelete();
            $table->string('code', 80);
            $table->string('name', 180);
            $table->integer('level')->default(1)->index();
            $table->string('type', 50)->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unique(['project_id', 'code']);
        });

        Schema::create('ref_vendor_type', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::create('ref_vendor', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->foreignId('vendor_type_id')->constrained('ref_vendor_type')->restrictOnDelete();
            $table->string('vendor_code', 50)->unique();
            $table->string('vendor_name', 180);
            $table->string('npwp', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('pic_name', 150)->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('bank_account', 120)->nullable();
            $table->boolean('is_active')->default(true)->index();
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
        Schema::dropIfExists('ref_vendor');
        Schema::dropIfExists('ref_vendor_type');
        Schema::dropIfExists('ref_cost_codes');
        Schema::dropIfExists('ref_project_sites');
        Schema::dropIfExists('ref_project');
        Schema::dropIfExists('ref_items');
        Schema::dropIfExists('ref_item_unit');
        Schema::dropIfExists('ref_item_categories');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('sys_permissions');
        Schema::dropIfExists('sys_roles');
        Schema::dropIfExists('sys_users');
        Schema::dropIfExists('ref_jabatan');
        Schema::dropIfExists('ref_units');
        Schema::enableForeignKeyConstraints();
    }

    private function masterColumns(Blueprint $table): void
    {
        $table->id();
        $table->timestamps();
        $table->softDeletes();
        $table->unsignedBigInteger('created_by')->nullable()->index();
        $table->unsignedBigInteger('updated_by')->nullable()->index();
        $table->unsignedBigInteger('deleted_by')->nullable()->index();
    }

    private function auditedTables(): array
    {
        return [
            'ref_units',
            'ref_jabatan',
            'sys_users',
            'sys_roles',
            'sys_permissions',
            'role_permissions',
            'ref_item_categories',
            'ref_item_unit',
            'ref_items',
            'ref_project',
            'ref_project_sites',
            'ref_cost_codes',
            'ref_vendor_type',
            'ref_vendor',
        ];
    }
};
