<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ListController extends Controller
{
    public function index()
    {
        $lists = GiftList::query()
            ->where('owner_id', Auth::id())
            ->orderByDesc('id')
            ->get();
        return response()->json($lists);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:200'],
            'description' => ['nullable','string'],
            'notes' => ['nullable','string'],
            'theme' => ['nullable','string','max:50'],
        ]);
        $list = GiftList::create($data + ['owner_id' => Auth::id()]);
        return response()->json($list, 201);
    }

    public function show(string $id)
    {
        $list = GiftList::findOrFail($id);
        if (! Gate::allows('view', $list)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return response()->json($list);
    }

    public function update(Request $request, string $id)
    {
        $list = GiftList::findOrFail($id);
        if (! Gate::allows('update', $list)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'title' => ['sometimes','required','string','max:200'],
            'description' => ['nullable','string'],
            'notes' => ['nullable','string'],
            'theme' => ['nullable','string','max:50'],
            'uses_people' => ['sometimes','boolean'],
            'show_sublist_in_main' => ['sometimes','boolean'],
        ]);
        $list->update($data);
        return response()->json($list);
    }

    public function destroy(string $id)
    {
        $list = GiftList::findOrFail($id);
        if (! Gate::allows('delete', $list)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $list->delete();
        return response()->json(['ok' => true]);
    }

    public function finalize(string $id)
    {
        $list = GiftList::findOrFail($id);
        if (! Gate::allows('update', $list)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $list->finalized_at = now();
        $list->save();
        return response()->json($list);
    }
}
