<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique();
            $table->string('subject', 180);
            $table->text('description');
            $table->foreignUuid('category_id')->nullable()->constrained('ref_ticket_categories')->nullOnDelete();
            $table->string('priority', 30)->index();
            $table->string('status', 30)->index();
            $table->string('source', 30)->index();
            $table->foreignUuid('requester_id')->constrained('sys_users')->restrictOnDelete();
            $table->foreignUuid('assigned_to')->nullable()->constrained('sys_users')->nullOnDelete();
            $table->foreignUuid('resolved_by')->nullable()->constrained('sys_users')->nullOnDelete();
            $table->foreignUuid('closed_by')->nullable()->constrained('sys_users')->nullOnDelete();
            $table->foreignUuid('jabatan_id')->nullable()->constrained('ref_jabatan')->nullOnDelete();
            $table->foreignUuid('sla_id')->nullable()->constrained('ref_ticket_slas')->nullOnDelete();
            $table->timestamp('response_due_at')->nullable();
            $table->timestamp('resolve_due_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('is_overdue')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
