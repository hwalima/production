<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('machine_runtimes', function (Blueprint $table) {
            $table->id();
            $table->string('machine_code');
            $table->string('description');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('service_after_hours');
            $table->date('next_service_date');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('machine_runtimes');
    }
};