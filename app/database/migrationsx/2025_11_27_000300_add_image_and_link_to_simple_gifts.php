<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('simple_gifts', function (Blueprint $table) {
            if (! Schema::hasColumn('simple_gifts', 'image_path')) {
                $table->string('image_path')->nullable()->after('name');
            }
            if (! Schema::hasColumn('simple_gifts', 'link_url')) {
                $table->string('link_url')->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('simple_gifts', function (Blueprint $table) {
            if (Schema::hasColumn('simple_gifts', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('simple_gifts', 'link_url')) {
                $table->dropColumn('link_url');
            }
        });
    }
};

