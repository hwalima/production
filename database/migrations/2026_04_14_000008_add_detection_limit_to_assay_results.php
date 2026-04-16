<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assay_results', function (Blueprint $table) {
            $table->decimal('detection_limit', 8, 4)->default(0.01)->after('assay_value');
        });
    }
    public function down(): void
    {
        Schema::table('assay_results', function (Blueprint $table) {
            $table->dropColumn('detection_limit');
        });
    }
};
