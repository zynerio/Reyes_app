<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('simple_gifts', function (Blueprint $table) {
            if (! Schema::hasColumn('simple_gifts', 'participant_id')) {
                $table->foreignId('participant_id')->nullable()->after('list_id')->constrained('participants')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('simple_gifts', function (Blueprint $table) {
            if (Schema::hasColumn('simple_gifts', 'participant_id')) {
                $table->dropForeign(['participant_id']);
                $table->dropColumn('participant_id');
            }
        });
    }
};

