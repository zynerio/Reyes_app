<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use App\Models\Recipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RecipientController extends Controller
{
    public function index(string $list)
    {
        $giftList = GiftList::findOrFail($list);
        if (! Gate::allows('view', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return response()->json($giftList->recipients()->orderBy('name')->get());
    }

    public function store(Request $request, string $list)
    {
        $giftList = GiftList::findOrFail($list);
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'note' => ['nullable','string'],
        ]);
        $recipient = $giftList->recipients()->create($data);
        return response()->json($recipient, 201);
    }

    public function show(string $id)
    {
        $recipient = Recipient::findOrFail($id);
        $giftList = $recipient->list;
        if (! Gate::allows('view', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return response()->json($recipient);
    }

    public function update(Request $request, string $id)
    {
        $recipient = Recipient::findOrFail($id);
        $giftList = $recipient->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'note' => ['nullable','string'],
        ]);
        $recipient->update($data);
        return response()->json($recipient);
    }

    public function destroy(string $id)
    {
        $recipient = Recipient::findOrFail($id);
        $giftList = $recipient->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $recipient->delete();
        return response()->json(['ok' => true]);
    }
}

