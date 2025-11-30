<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            if (! Schema::hasColumn('lists', 'show_sublist_in_main')) {
                $table->boolean('show_sublist_in_main')->default(true)->after('uses_people');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            if (Schema::hasColumn('lists', 'show_sublist_in_main')) {
                $table->dropColumn('show_sublist_in_main');
            }
        });
    }
};

