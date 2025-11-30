<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\GiftList;
use App\Models\Participant;

class PeopleController extends Controller
{
    public function index()
    {
        return response()->json(Person::where('user_id', auth()->id())->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'contact' => ['nullable','string','max:150'],
            'note' => ['nullable','string'],
            'color_key' => ['nullable','in:rose,amber,blue,green,purple,cyan,fuchsia,indigo,lime,teal'],
        ]);
        if (!isset($data['color_key'])) {
            $palette = ['rose','amber','blue','green','purple','cyan','fuchsia','indigo','lime','teal'];
            $data['color_key'] = $palette[random_int(0, count($palette)-1)];
        }
        $p = Person::create($data + ['user_id' => auth()->id()]);
        return response()->json($p, 201);
    }

    public function update(Request $request, string $id)
    {
        $p = Person::where('user_id', auth()->id())->findOrFail($id);
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'contact' => ['nullable','string','max:150'],
            'note' => ['nullable','string'],
            'color_key' => ['nullable','in:rose,amber,blue,green,purple,cyan,fuchsia,indigo,lime,teal'],
        ]);
        $p->update($data);
        return response()->json($p);
    }

    public function destroy(string $id)
    {
        $p = Person::where('user_id', auth()->id())->findOrFail($id);
        $p->delete();
        return response()->json(['ok' => true]);
    }

    public function lists(string $id)
    {
        $p = Person::where('user_id', auth()->id())->findOrFail($id);
        $lists = GiftList::whereHas('participants', function($q) use ($p) {
            $q->where(function($qq) use ($p) {
                $qq->where('person_id', $p->id)
                   ->orWhere('name', $p->name);
            });
        })->get();
        return response()->json($lists);
    }

    public function attachToList(Request $request, string $list)
    {
        $giftList = GiftList::findOrFail($list);
        $data = $request->validate([
            'person_id' => ['required','exists:people,id'],
        ]);
        $p = Person::where('user_id', auth()->id())->findOrFail($data['person_id']);
        $exists = Participant::where('list_id', $giftList->id)->where('person_id', $p->id)->exists();
        if (! $exists) {
            Participant::create([
                'list_id' => $giftList->id,
                'person_id' => $p->id,
                'name' => $p->name,
                'contact' => $p->contact,
                'note' => $p->note,
            ]);
        }
        return response()->json(['ok' => true]);
    }

    public function detachFromList(string $list, string $person)
    {
        $giftList = GiftList::findOrFail($list);
        $p = Person::where('user_id', auth()->id())->findOrFail($person);
        Participant::where('list_id', $giftList->id)->where('person_id', $p->id)->delete();
        return response()->json(['ok' => true]);
    }
}
