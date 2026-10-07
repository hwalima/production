<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('machine_runtimes', function (Blueprint $table) {
            $table->date('next_service_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('machine_runtimes')
            ->whereNull('next_service_date')
            ->orderBy('id')
            ->chunkById(500, function ($runtimes) {
                foreach ($runtimes as $runtime) {
                    DB::table('machine_runtimes')
                        ->where('id', $runtime->id)
                        ->update([
                            'next_service_date' => Carbon::parse($runtime->end_time)
                                ->addDays((int) $runtime->service_after_hours)
                                ->toDateString(),
                        ]);
                }
            });

        Schema::table('machine_runtimes', function (Blueprint $table) {
            $table->date('next_service_date')->nullable(false)->change();
        });
    }
};
