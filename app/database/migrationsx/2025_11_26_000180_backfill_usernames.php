<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $users = DB::table('users')->whereNull('username')->get(['id', 'email']);
        foreach ($users as $u) {
            $local = explode('@', $u->email)[0];
            $base = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $local));
            if (strlen($base) < 3) {
                $base = 'user'.$u->id;
            }
            $candidate = $base;
            $i = 1;
            while (DB::table('users')->where('username', $candidate)->exists()) {
                $candidate = $base.$i;
                $i++;
            }
            DB::table('users')->where('id', $u->id)->update(['username' => $candidate]);
        }
    }

    public function down(): void
    {
        // no-op
    }
};

