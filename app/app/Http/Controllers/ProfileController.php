<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function me()
    {
        return response()->json(['user' => auth()->user()]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current' => ['required','string'],
            'password' => ['required','string','min:8','confirmed'],
        ]);
        $user = auth()->user();
        if (! Hash::check($data['current'], $user->password)) {
            return response()->json(['message' => 'Contraseña actual incorrecta'], 422);
        }
        $user->password = Hash::make($data['password']);
        $user->save();
        return response()->json(['ok' => true]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['nullable','file','mimes:jpg,jpeg,png','max:2048'],
        ]);
        $user = auth()->user();
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            try {
                Storage::disk('public')->makeDirectory('avatars');
                $path = $request->file('avatar')->store('avatars', 'public');
                \Log::info('avatar_upload', [
                    'path' => $path,
                    'full' => storage_path('app/public/'.str_replace('avatars/','avatars/',$path)),
                    'exists' => Storage::disk('public')->exists($path),
                ]);
                if (! Storage::disk('public')->exists($path)) {
                    return response()->json(['message' => 'Error guardando avatar'], 500);
                }
                $dest = public_path('storage/'.str_replace('\\','/',$path));
                File::ensureDirectoryExists(dirname($dest));
                File::copy(Storage::disk('public')->path($path), $dest);
                $user->avatar_path = $path;
                $user->save();
                return response()->json(['avatar' => $path]);
            } catch (\Throwable $e) {
                \Log::error('avatar_upload_error', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Error guardando avatar', 'error' => $e->getMessage()], 500);
            }
        }
        return response()->json(['message' => 'Sin archivo'], 422);
    }

    public function deleteAvatar()
    {
        $user = auth()->user();
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }
        return response()->json(['ok' => true]);
    }
}
