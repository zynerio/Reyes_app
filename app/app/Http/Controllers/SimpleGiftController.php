<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use App\Models\SimpleGift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SimpleGiftController extends Controller
{
    public function index(string $list)
    {
        $giftList = GiftList::findOrFail($list);
        if (! Gate::allows('view', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $query = SimpleGift::query()->where('list_id', $giftList->id)->orderByDesc('id');
        if (request()->has('participant')) {
            $query->where('participant_id', request('participant'));
        }
        if (request()->boolean('only_main')) {
            $query->whereNull('participant_id');
        }
        return response()->json($query->get());
    }

    public function store(Request $request, string $list)
    {
        $giftList = GiftList::findOrFail($list);
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'name' => ['required','string','max:200'],
            'price' => ['required','numeric','min:0'],
            'link_url' => ['nullable','string'],
            'is_ordered' => ['nullable','boolean'],
            'is_received' => ['nullable','boolean'],
            'participant_id' => ['nullable','exists:participants,id'],
        ]);
        $gift = SimpleGift::create($data + ['list_id' => $giftList->id]);
        return response()->json($gift, 201);
    }

    public function update(Request $request, string $id)
    {
        $gift = SimpleGift::findOrFail($id);
        $giftList = $gift->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $data = $request->validate([
            'name' => ['sometimes','string','max:200'],
            'price' => ['sometimes','numeric','min:0'],
            'link_url' => ['sometimes','nullable','string'],
            'is_ordered' => ['sometimes','boolean'],
            'is_received' => ['sometimes','boolean'],
            'participant_id' => ['sometimes','nullable','exists:participants,id'],
        ]);
        $gift->update($data);
        return response()->json($gift);
    }

    public function destroy(string $id)
    {
        $gift = SimpleGift::findOrFail($id);
        $giftList = $gift->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $gift->delete();
        return response()->json(['ok' => true]);
    }

    public function destroyByParticipant(string $list, string $participant)
    {
        $giftList = GiftList::findOrFail($list);
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        SimpleGift::where('list_id', $giftList->id)->where('participant_id', $participant)->delete();
        return response()->json(['ok' => true]);
    }

    public function uploadImage(Request $request, string $id)
    {
        $gift = SimpleGift::findOrFail($id);
        $giftList = $gift->list;
        if (! Gate::allows('update', $giftList)) {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        $request->validate([
            'image' => ['required','file','mimes:jpeg,jpg,png','max:5120'],
        ]);
        try {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('gifts');
            $path = $request->file('image')->store('gifts', 'public');
            \Illuminate\Support\Facades\Log::info('gift_upload', [
                'path' => $path,
                'full' => storage_path('app/public/'.str_replace('gifts/','gifts/',$path)),
                'exists' => \Illuminate\Support\Facades\Storage::disk('public')->exists($path),
            ]);
            if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return response()->json(['message' => 'Error guardando imagen'], 500);
            }
            $dest = public_path('storage/'.str_replace('\\','/',$path));
            \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($dest));
            \Illuminate\Support\Facades\File::copy(\Illuminate\Support\Facades\Storage::disk('public')->path($path), $dest);
            $gift->update(['image_path' => $path]);
            return response()->json($gift);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('gift_upload_error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error guardando imagen', 'error' => $e->getMessage()], 500);
        }
    }
}
