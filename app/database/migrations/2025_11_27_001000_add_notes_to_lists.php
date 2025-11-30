<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            if (! Schema::hasColumn('lists', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            if (Schema::hasColumn('lists', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};

