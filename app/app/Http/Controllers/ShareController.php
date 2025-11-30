<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use App\Models\Invitation;
use App\Models\ListShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ShareController extends Controller
{
    public function index(string $id)
    {
        $list = GiftList::findOrFail($id);
        if (! Gate::allows('view', $list)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return response()->json($list->shares()->with('user:id,name,email')->get());
    }

    public function store(Request $request, string $id)
    {
        $list = GiftList::findOrFail($id);
        if (! Gate::allows('share', $list)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'email' => ['required','email'],
            'permission' => ['required','in:read,write'],
        ]);
        $token = Str::uuid()->toString();
        $inv = Invitation::create([
            'list_id' => $list->id,
            'email' => $data['email'],
            'token' => $token,
            'status' => 'pending',
        ]);
        return response()->json($inv, 201);
    }

    public function update(Request $request, string $id)
    {
        $share = ListShare::findOrFail($id);
        $list = $share->list;
        if (! Gate::allows('share', $list)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'permission' => ['required','in:read,write'],
        ]);
        $share->update($data);
        return response()->json($share);
    }
}

