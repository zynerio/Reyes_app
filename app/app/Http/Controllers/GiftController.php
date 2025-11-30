<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use App\Models\Gift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GiftController extends Controller
{
    public function index(string $list)
    {
        $giftList = GiftList::findOrFail($list);
        if (! Gate::allows('view', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return response()->json($giftList->gifts()->with('recipient')->orderByDesc('id')->get());
    }

    public function store(Request $request, string $list)
    {
        $giftList = GiftList::findOrFail($list);
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'recipient_id' => ['required','exists:recipients,id'],
            'name' => ['required','string','max:200'],
            'price' => ['required','numeric','min:0'],
            'is_ordered' => ['nullable','boolean'],
            'is_received' => ['nullable','boolean'],
            'note' => ['nullable','string'],
            'participant_id' => ['required','exists:participants,id'],
        ]);
        $gift = $giftList->gifts()->create($data);
        \App\Models\GiftAssignment::firstOrCreate([
            'gift_id' => $gift->id,
            'participant_id' => $data['participant_id'],
        ]);
        return response()->json($gift->load('recipient'), 201);
    }

    public function show(string $id)
    {
        $gift = Gift::findOrFail($id);
        $giftList = $gift->list;
        if (! Gate::allows('view', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return response()->json($gift->load('recipient'));
    }

    public function update(Request $request, string $id)
    {
        $gift = Gift::findOrFail($id);
        $giftList = $gift->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'recipient_id' => ['required','exists:recipients,id'],
            'name' => ['required','string','max:200'],
            'price' => ['required','numeric','min:0'],
            'is_ordered' => ['nullable','boolean'],
            'is_received' => ['nullable','boolean'],
            'note' => ['nullable','string'],
        ]);
        $gift->update($data);
        return response()->json($gift->load('recipient'));
    }

    public function destroy(string $id)
    {
        $gift = Gift::findOrFail($id);
        $giftList = $gift->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $gift->delete();
        return response()->json(['ok' => true]);
    }
}
