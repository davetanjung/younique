<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClothController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlannerController;

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::post('/', [HomeController::class, 'post'])->name('home.post');

Route::get('/planner', [PlannerController::class, 'index'])->name('planner.index');
Route::get('/planner-data', [PlannerController::class, 'getMonthlyEntries']);
Route::post('/planner/regenerate-outfit', [PlannerController::class, 'regenerateOutfit']);
Route::post('/planner/save', [PlannerController::class, 'save']);
Route::post('/planner/generate-monthly', [PlannerController::class, 'generateMonthlyOutfits']);

// auth
Route::get('/login', [AuthController::class, 'show'])->name('login.show');
Route::post('/login_auth', [AuthController::class, 'login_auth'])->name('login.auth');
Route::get('/login', [AuthController::class, 'show'])->name('login.show')->middleware('guest');
Route::get('/register', function () {
        return view('home.register');
});

// wardrobe
Route::get('/wardrobe', function () {
        return view('e-wardrobe.e-wardrobe');
});
Route::get('/myclothes', [ClothController::class, 'index'])->name('cloth.index');
Route::post('/myclothes', [ClothController::class, 'store'])->name('cloth.store');
Route::get('/myfavorites', function () {
        return view('e-wardrobe.favorites');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/stylist', [StylistController::class, 'index'])->name('stylist.index');
});

require __DIR__.'/auth.php';
