<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('action_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mining_department_id')
                  ->constrained('mining_departments')
                  ->onDelete('cascade');
            $table->text('comment');
            $table->enum('priority', ['high', 'medium', 'low'])->default('high');
            $table->enum('status', ['not_started', 'in_progress', 'pending', 'completed'])->default('not_started');
            $table->date('due_date')->nullable();
            $table->date('reported_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_items');
    }
};
