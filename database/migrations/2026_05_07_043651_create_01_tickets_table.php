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
            $table->foreignId('category_id')->nullable()->constrained('ref_ticket_categories')->nullOnDelete();
            $table->string('priority', 30)->index();
            $table->string('status', 30)->index();
            $table->string('source', 30)->index();
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('jabatan_id')->nullable()->constrained('ref_jabatan')->nullOnDelete();
            $table->foreignId('sla_id')->nullable()->constrained('ref_ticket_slas')->nullOnDelete();
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
