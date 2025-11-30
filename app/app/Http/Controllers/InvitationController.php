<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\ListShare;
use App\Models\User;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function accept(Request $request, string $token)
    {
        $inv = Invitation::where('token', $token)->firstOrFail();
        $inv->status = 'accepted';
        $inv->save();
        $user = User::where('email', $inv->email)->first();
        if ($user) {
            ListShare::firstOrCreate([
                'list_id' => $inv->list_id,
                'user_id' => $user->id,
            ], [
                'permission' => 'read',
            ]);
        }
        return response()->json(['ok' => true]);
    }
}

