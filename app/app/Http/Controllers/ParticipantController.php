<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ParticipantController extends Controller
{
    public function index(string $list)
    {
        $giftList = GiftList::findOrFail($list);
        if (! Gate::allows('view', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return response()->json($giftList->participants()->orderBy('name')->get());
    }

    public function store(Request $request, string $list)
    {
        $giftList = GiftList::findOrFail($list);
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'contact' => ['nullable','string','max:150'],
            'note' => ['nullable','string'],
        ]);
        $participant = $giftList->participants()->create($data);
        return response()->json($participant, 201);
    }

    public function show(string $id)
    {
        $participant = Participant::findOrFail($id);
        $giftList = $participant->list;
        if (! Gate::allows('view', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return response()->json($participant);
    }

    public function update(Request $request, string $id)
    {
        $participant = Participant::findOrFail($id);
        $giftList = $participant->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'contact' => ['nullable','string','max:150'],
            'note' => ['nullable','string'],
        ]);
        $participant->update($data);
        return response()->json($participant);
    }

    public function destroy(string $id)
    {
        $participant = Participant::findOrFail($id);
        $giftList = $participant->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $participant->delete();
        return response()->json(['ok' => true]);
    }
}

