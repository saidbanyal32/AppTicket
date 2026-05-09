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
        Schema::create('help_article_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('help_articles')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_name');
            $table->string('file_extension', 30)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size');
            $table->string('disk', 50);
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('sys_users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_article_attachments');
    }
};
