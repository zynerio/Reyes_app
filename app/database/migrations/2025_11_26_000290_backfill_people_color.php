<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $palette = ['rose','amber','blue','green','purple','cyan','fuchsia','indigo','lime','teal'];
        $rows = DB::table('people')->whereNull('color_key')->get(['id','name']);
        foreach ($rows as $r) {
            $name = $r->name ?? '';
            $hash = 0;
            for ($i = 0; $i < strlen($name); $i++) {
                $hash = (($hash << 5) - $hash) + ord($name[$i]);
            }
            $idx = abs($hash) % count($palette);
            DB::table('people')->where('id', $r->id)->update(['color_key' => $palette[$idx]]);
        }
    }

    public function down(): void
    {
        DB::table('people')->update(['color_key' => null]);
    }
};

