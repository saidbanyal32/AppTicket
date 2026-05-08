<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_ticket_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('ref_ticket_categories')->nullOnDelete();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->string('color', 30)->nullable();
            $table->string('icon', 80)->nullable();
            $table->foreignUuid('sla_id')->nullable()->constrained('ref_ticket_slas')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_no')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('created_by')->nullable()->index();
            $table->uuid('updated_by')->nullable()->index();
            $table->uuid('deleted_by')->nullable()->index();
        });

        Schema::table('ref_ticket_categories', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('sys_users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('sys_users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('sys_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_ticket_categories');
    }
};
