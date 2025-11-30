<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            if (! Schema::hasColumn('lists', 'uses_people')) {
                $table->boolean('uses_people')->default(false)->after('theme');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            if (Schema::hasColumn('lists', 'uses_people')) {
                $table->dropColumn('uses_people');
            }
        });
    }
};

