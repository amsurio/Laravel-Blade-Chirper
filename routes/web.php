<?php

use App\Http\Controllers\Auth\AuthTokenController;
use App\Http\Controllers\ChirpController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('chirps', ChirpController::class)->only(['index', 'store', 'edit', 'update', 'destroy'])->middleware(['auth', 'verified']);


Route::get('login', [AuthTokenController::class, 'redirectToProvider'])->name('login');
Route::get('callback', [AuthTokenController::class, 'handleProviderCallback']);

Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [AuthTokenController::class, 'register']);



// require __DIR__ . '/auth.php';
