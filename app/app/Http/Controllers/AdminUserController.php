<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        return response()->json(User::query()->orderBy('id')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => ['required','email','max:255','unique:users,email'],
            'username' => ['nullable','alpha_dash','min:3','max:30','unique:users,username'],
            'password' => ['required','string','min:8'],
            'role' => ['nullable','in:user,admin'],
        ]);
        $username = $data['username'] ?? null;
        if (!$username) {
            $local = explode('@', $data['email'])[0];
            $base = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $local));
            if (strlen($base) < 3) {
                $base = 'user'.rand(1000,9999);
            }
            $candidate = $base;
            $i = 1;
            while (User::where('username', $candidate)->exists()) {
                $candidate = $base.$i;
                $i++;
            }
            $username = $candidate;
        }
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $username,
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'user',
        ]);
        return response()->json($user, 201);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => ['sometimes','string','max:100'],
            'email' => ['sometimes','email','max:255','unique:users,email,'.$user->id],
            'username' => ['sometimes','alpha_dash','min:3','max:30','unique:users,username,'.$user->id],
            'role' => ['sometimes','in:user,admin'],
        ]);
        $user->update($data);
        return response()->json($user);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $current = auth()->user();
        if ($current && $user->id === $current->id) {
            return response()->json(['message' => 'No puedes eliminarte a ti mismo'], 422);
        }
        try {
            $user->delete();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'No se pudo eliminar el usuario', 'error' => $e->getMessage()], 422);
        }
    }
}
