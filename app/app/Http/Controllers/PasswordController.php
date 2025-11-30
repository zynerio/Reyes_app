<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PasswordController extends Controller
{
    public function forgot(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
        ]);
        $token = Str::uuid()->toString();
        DB::table('password_resets')->updateOrInsert(
            ['email' => $data['email']],
            ['token' => $token, 'created_at' => now()]
        );
        return response()->json(['token' => $token]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required','string'],
            'password' => ['required','string','min:8','confirmed'],
        ]);
        $rec = DB::table('password_resets')->where('token', $data['token'])->first();
        if (! $rec) {
            return response()->json(['message' => 'Token inválido'], 422);
        }
        $user = User::where('email', $rec->email)->first();
        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $user->password = Hash::make($data['password']);
        $user->save();
        DB::table('password_resets')->where('email', $rec->email)->delete();
        return response()->json(['ok' => true]);
    }
}

