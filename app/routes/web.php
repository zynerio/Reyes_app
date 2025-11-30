<?php

use Illuminate\Support\Facades\Route;
use App\Models\GiftList;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect('/login');
    }
    $lists = GiftList::query()->where('owner_id', auth()->id())->orderByDesc('id')->get();
    return view('dashboard', ['lists' => $lists]);
});

Route::get('/lists/{id}', function (string $id) {
    $list = GiftList::with(['participants.person'])->findOrFail($id);
    return view('list', ['list' => $list]);
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});
Route::get('/password/forgot', function () {
    return view('auth.forgot');
});

Route::get('/profile', function () {
    if (! auth()->check()) {
        return redirect('/login');
    }
    return view('profile');
});

Route::get('/admin/users', function () {
    if (! auth()->check() || auth()->user()->role !== 'admin') {
        return redirect('/');
    }
    return view('admin.users');
});
Route::get('/people', function () {
    if (! auth()->check()) {
        return redirect('/login');
    }
    return view('people');
});
