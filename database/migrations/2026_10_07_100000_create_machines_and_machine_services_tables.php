<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('machine_code')->unique();
            $table->string('description');
            $table->unsignedInteger('service_interval_hours');
            $table->dateTime('service_tracking_started_at')->nullable();
            $table->dateTime('service_alert_sent_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('machine_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->restrictOnDelete();
            $table->dateTime('serviced_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['machine_id', 'serviced_at']);
        });

        Schema::table('machine_runtimes', function (Blueprint $table) {
            $table->foreignId('machine_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->decimal('hours_run', 10, 2)->default(0)->after('end_time');
        });

        $machineIds = [];
        $codes = DB::table('machine_runtimes')
            ->select('machine_code')
            ->distinct()
            ->pluck('machine_code');

        foreach ($codes as $code) {
            $normalizedCode = strtolower(trim($code));
            if (isset($machineIds[$normalizedCode])) {
                continue;
            }

            $latest = DB::table('machine_runtimes')
                ->whereRaw('LOWER(TRIM(machine_code)) = ?', [$normalizedCode])
                ->orderByDesc('end_time')
                ->orderByDesc('id')
                ->first();

            if (!$latest) {
                continue;
            }

            $machineId = DB::table('machines')->insertGetId([
                'machine_code' => trim($latest->machine_code),
                'description' => $latest->description,
                'service_interval_hours' => max(1, (int) $latest->service_after_hours),
                'service_tracking_started_at' => $latest->end_time,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $machineIds[$normalizedCode] = $machineId;
        }

        DB::table('machine_runtimes')->orderBy('id')->chunk(500, function ($runtimes) use ($machineIds) {
            foreach ($runtimes as $runtime) {
                $start = Carbon::parse($runtime->start_time)->getTimestamp();
                $end = Carbon::parse($runtime->end_time)->getTimestamp();
                $hoursRun = $end > $start ? round(($end - $start) / 3600, 2) : 0;

                DB::table('machine_runtimes')
                    ->where('id', $runtime->id)
                    ->update([
                        'machine_id' => $machineIds[strtolower(trim($runtime->machine_code))] ?? null,
                        'hours_run' => $hoursRun,
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('machine_runtimes', function (Blueprint $table) {
            $table->dropForeign(['machine_id']);
            $table->dropColumn(['machine_id', 'hours_run']);
        });

        Schema::dropIfExists('machine_services');
        Schema::dropIfExists('machines');
    }
};
