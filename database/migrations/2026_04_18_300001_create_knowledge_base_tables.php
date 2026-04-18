<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\KnowledgeBaseSeeder;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('slug', 120)->unique();
            $table->string('icon', 20)->default('📄');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('knowledge_base_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_base_category_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('slug', 200);
            $table->longText('content');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->unique(['knowledge_base_category_id', 'slug']);
        });

        (new KnowledgeBaseSeeder())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_articles');
        Schema::dropIfExists('knowledge_base_categories');
    }
};
