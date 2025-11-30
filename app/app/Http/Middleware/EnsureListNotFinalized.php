<?php

namespace App\Http\Middleware;

use App\Models\GiftList;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureListNotFinalized
{
    public function handle(Request $request, Closure $next): Response
    {
        $listId = $request->route('list') ?? $request->route('id') ?? $request->input('list_id');
        if ($listId) {
            $list = GiftList::query()->find($listId);
            if ($list && $list->finalized_at !== null && in_array($request->method(), ['POST','PUT','PATCH','DELETE'])) {
                return response()->json(['message' => 'Lista finalizada'], 423);
            }
        }
        return $next($request);
    }
}

