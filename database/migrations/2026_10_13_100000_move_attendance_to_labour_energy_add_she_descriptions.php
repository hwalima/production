<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labour_energy', function (Blueprint $table) {
            $table->decimal('leave', 8, 2)->default(0);
            $table->decimal('offdays', 8, 2)->default(0);
            $table->decimal('sick', 8, 2)->default(0);
            $table->decimal('awol', 8, 2)->default(0);
            $table->decimal('terminations', 8, 2)->default(0);
        });

        Schema::table('she_indicators', function (Blueprint $table) {
            $table->decimal('environmental_incidents_penalties', 8, 2)->default(0);
            foreach ([
                'medical_injury_case',
                'fatal_incident',
                'lti',
                'nlti',
                'iod',
                'environmental_incidents_penalties',
            ] as $field) {
                $table->text($field . '_description')->nullable();
            }
        });

        $attendanceByDate = DB::table('she_indicators')
            ->selectRaw('date, SUM(`leave`) as `leave`, SUM(offdays) as offdays, SUM(sick) as sick, SUM(awol) as awol, SUM(terminations) as terminations')
            ->groupBy('date')
            ->get();

        foreach ($attendanceByDate as $attendance) {
            $record = DB::table('labour_energy')->whereDate('date', $attendance->date)->first();
            $values = [
                'leave' => $attendance->leave,
                'offdays' => $attendance->offdays,
                'sick' => $attendance->sick,
                'awol' => $attendance->awol,
                'terminations' => $attendance->terminations,
                'updated_at' => now(),
            ];

            if ($record) {
                DB::table('labour_energy')->where('id', $record->id)->update($values);
            } else {
                DB::table('labour_energy')->insert(array_merge($values, [
                    'date' => $attendance->date,
                    'zesa_cost' => 0,
                    'diesel_cost' => 0,
                    'labour_cost' => 0,
                    'created_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::table('she_indicators', function (Blueprint $table) {
            $table->dropColumn([
                'environmental_incidents_penalties',
                'medical_injury_case_description',
                'fatal_incident_description',
                'lti_description',
                'nlti_description',
                'iod_description',
                'environmental_incidents_penalties_description',
            ]);
        });

        Schema::table('labour_energy', function (Blueprint $table) {
            $table->dropColumn(['leave', 'offdays', 'sick', 'awol', 'terminations']);
        });
    }
};
