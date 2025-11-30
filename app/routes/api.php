<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\SimpleGiftController;

Route::post('/auth/register', function (Request $request) {
    $data = $request->validate([
        'name' => ['required','string','max:100'],
        'email' => ['required','email','max:255','unique:users,email'],
        'username' => ['nullable','alpha_dash','min:3','max:30','unique:users,username'],
        'password' => ['required','string','min:8'],
    ]);
    $username = $data['username'] ?? null;
    if (!$username) {
        $local = explode('@', $data['email'])[0];
        $base = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $local));
        if (strlen($base) < 3) {
            $base = 'user'.rand(1000,9999);
        }
        $candidate = $base;
        $i = 1;
        while (User::where('username', $candidate)->exists()) {
            $candidate = $base.$i;
            $i++;
        }
        $username = $candidate;
    }
    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'username' => $username,
        'password' => Hash::make($data['password']),
    ]);
    \Illuminate\Support\Facades\Auth::login($user);
    return response()->json(['user' => $user]);
});

Route::post('/auth/login', function (Request $request) {
    $data = $request->validate([
        'login' => ['required','string'],
        'password' => ['required','string'],
    ]);
    $user = User::where('email', $data['login'])->first();
    if (!$user) {
        $user = User::where('username', $data['login'])->first();
    }
    if (!$user || !Hash::check($data['password'], $user->password)) {
        return response()->json(['message' => 'Credenciales inválidas'], 422);
    }
    \Illuminate\Support\Facades\Auth::login($user);
    return response()->json(['user' => $user]);
});

Route::post('/auth/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    return response()->json(['ok' => true]);
});

Route::get('/auth/me', function () {
    return response()->json(['user' => \Illuminate\Support\Facades\Auth::user()]);
});

Route::apiResource('lists', ListController::class);
Route::post('lists/{id}/finalize', [ListController::class, 'finalize'])->middleware('list.unlocked');

Route::get('lists/{list}/recipients', [RecipientController::class, 'index']);
Route::post('lists/{list}/recipients', [RecipientController::class, 'store'])->middleware('list.unlocked');
Route::get('recipients/{id}', [RecipientController::class, 'show']);
Route::put('recipients/{id}', [RecipientController::class, 'update'])->middleware('list.unlocked');
Route::delete('recipients/{id}', [RecipientController::class, 'destroy'])->middleware('list.unlocked');

Route::get('lists/{list}/gifts', [GiftController::class, 'index']);
Route::post('lists/{list}/gifts', [GiftController::class, 'store'])->middleware('list.unlocked');
Route::get('gifts/{id}', [GiftController::class, 'show']);
Route::put('gifts/{id}', [GiftController::class, 'update'])->middleware('list.unlocked');
Route::delete('gifts/{id}', [GiftController::class, 'destroy'])->middleware('list.unlocked');

Route::get('lists/{list}/participants', [ParticipantController::class, 'index']);
Route::post('lists/{list}/participants', [ParticipantController::class, 'store'])->middleware('list.unlocked');
Route::get('participants/{id}', [ParticipantController::class, 'show']);
Route::put('participants/{id}', [ParticipantController::class, 'update'])->middleware('list.unlocked');
Route::delete('participants/{id}', [ParticipantController::class, 'destroy'])->middleware('list.unlocked');

Route::get('gifts/{id}/assignments', [AssignmentController::class, 'index']);
Route::post('gifts/{id}/assignments', [AssignmentController::class, 'store'])->middleware('list.unlocked');
Route::delete('assignments/{id}', [AssignmentController::class, 'destroy'])->middleware('list.unlocked');

Route::post('lists/{id}/share', [ShareController::class, 'store']);
Route::get('lists/{id}/shares', [ShareController::class, 'index']);
Route::patch('shares/{id}', [ShareController::class, 'update']);

Route::post('invitations/{token}/accept', [InvitationController::class, 'accept']);
Route::post('auth/password/forgot', [PasswordController::class, 'forgot']);
Route::post('auth/password/reset', [PasswordController::class, 'reset']);

Route::middleware('auth')->group(function () {
    Route::get('people', [PeopleController::class, 'index']);
    Route::post('people', [PeopleController::class, 'store']);
    Route::put('people/{id}', [PeopleController::class, 'update']);
    Route::delete('people/{id}', [PeopleController::class, 'destroy']);
    Route::get('people/{id}/lists', [PeopleController::class, 'lists']);
    Route::post('lists/{list}/people', [PeopleController::class, 'attachToList']);
    Route::delete('lists/{list}/people/{person}', [PeopleController::class, 'detachFromList']);
    Route::get('profile', [ProfileController::class, 'me']);
    Route::post('profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar']);
    Route::apiResource('lists', ListController::class);
    Route::post('lists/{id}/finalize', [ListController::class, 'finalize'])->middleware('list.unlocked');

    Route::get('lists/{list}/recipients', [RecipientController::class, 'index']);
    Route::post('lists/{list}/recipients', [RecipientController::class, 'store'])->middleware('list.unlocked');
    Route::get('recipients/{id}', [RecipientController::class, 'show']);
    Route::put('recipients/{id}', [RecipientController::class, 'update'])->middleware('list.unlocked');
    Route::delete('recipients/{id}', [RecipientController::class, 'destroy'])->middleware('list.unlocked');

    Route::get('lists/{list}/gifts', [GiftController::class, 'index']);
    Route::post('lists/{list}/gifts', [GiftController::class, 'store'])->middleware('list.unlocked');
    Route::get('gifts/{id}', [GiftController::class, 'show']);
    Route::put('gifts/{id}', [GiftController::class, 'update'])->middleware('list.unlocked');
    Route::delete('gifts/{id}', [GiftController::class, 'destroy'])->middleware('list.unlocked');

    Route::get('lists/{list}/participants', [ParticipantController::class, 'index']);
    Route::post('lists/{list}/participants', [ParticipantController::class, 'store'])->middleware('list.unlocked');
    Route::get('participants/{id}', [ParticipantController::class, 'show']);
    Route::put('participants/{id}', [ParticipantController::class, 'update'])->middleware('list.unlocked');
    Route::delete('participants/{id}', [ParticipantController::class, 'destroy'])->middleware('list.unlocked');

    Route::get('gifts/{id}/assignments', [AssignmentController::class, 'index']);
    Route::post('gifts/{id}/assignments', [AssignmentController::class, 'store'])->middleware('list.unlocked');
    Route::delete('assignments/{id}', [AssignmentController::class, 'destroy'])->middleware('list.unlocked');

    Route::post('lists/{id}/share', [ShareController::class, 'store']);
    Route::get('lists/{id}/shares', [ShareController::class, 'index']);
    Route::patch('shares/{id}', [ShareController::class, 'update']);

    Route::middleware('admin')->group(function () {
        Route::get('admin/users', [AdminUserController::class, 'index']);
        Route::post('admin/users', [AdminUserController::class, 'store']);
        Route::put('admin/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('admin/users/{id}', [AdminUserController::class, 'destroy']);
    });

    Route::get('lists/{list}/simple-gifts', [SimpleGiftController::class, 'index']);
    Route::post('lists/{list}/simple-gifts', [SimpleGiftController::class, 'store'])->middleware('list.unlocked');
    Route::put('simple-gifts/{id}', [SimpleGiftController::class, 'update'])->middleware('list.unlocked');
    Route::post('simple-gifts/{id}/image', [SimpleGiftController::class, 'uploadImage'])->middleware('list.unlocked');
    Route::delete('simple-gifts/{id}', [SimpleGiftController::class, 'destroy'])->middleware('list.unlocked');
    Route::delete('lists/{list}/simple-gifts/by-participant/{participant}', [SimpleGiftController::class, 'destroyByParticipant'])->middleware('list.unlocked');
});
