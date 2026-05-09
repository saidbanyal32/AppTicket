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
        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('help_categories')->restrictOnDelete();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->text('short_description')->nullable();
            $table->longText('content');
            $table->string('article_type', 50)->index();
            $table->string('visibility', 50)->index();
            $table->json('tags')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('sys_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('sys_users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['article_type', 'is_published']);
            $table->index(['category_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_articles');
    }
};
