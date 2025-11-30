<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use App\Models\GiftAssignment;
use App\Models\Participant;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(string $id)
    {
        $gift = Gift::findOrFail($id);
        $giftList = $gift->list;
        if (! Gate::allows('view', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return response()->json($gift->participants()->get());
    }

    public function store(Request $request, string $id)
    {
        $gift = Gift::findOrFail($id);
        $giftList = $gift->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'participant_id' => ['required','exists:participants,id'],
        ]);
        $participant = Participant::findOrFail($data['participant_id']);
        if ($participant->list_id !== $giftList->id) {
            return response()->json(['message' => 'Participante no pertenece a la lista'], 422);
        }
        GiftAssignment::firstOrCreate([
            'gift_id' => $gift->id,
            'participant_id' => $participant->id,
        ]);
        return response()->json(['ok' => true]);
    }

    public function destroy(string $id)
    {
        $assignment = GiftAssignment::findOrFail($id);
        $giftList = $assignment->gift->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $assignment->delete();
        return response()->json(['ok' => true]);
    }
}

