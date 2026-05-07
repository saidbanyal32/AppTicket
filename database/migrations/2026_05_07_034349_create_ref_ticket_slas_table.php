<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_ticket_slas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('priority', 50)->index();
            $table->unsignedInteger('response_minutes');
            $table->unsignedInteger('resolve_minutes');
            $table->unsignedInteger('escalation_minutes')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->unsignedBigInteger('deleted_by')->nullable()->index();
        });

        Schema::table('ref_ticket_slas', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('sys_users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('sys_users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('sys_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_ticket_slas');
    }
};
