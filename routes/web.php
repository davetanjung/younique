<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/', [HomeController::class, 'post'])->name('home.post');

Route::get('/login',[AuthController::class, 'show'])->name('login.show');
Route::post('/login_auth', [AuthController::class, 'login_auth'])->name('login.auth');
Route::get('/login', [AuthController::class,'show'])->name('login.show')->middleware('guest');
Route::get('/register', function () {
        return view('home.register') ;});
Route::get('/wardrobe', function () {
        return view('e-wardrobe.e-wardrobe') ;});
Route::get('/myclothes', function () {
    return view('e-wardrobe.myclothes') ;});
Route::get('/myfavorites', function () {
    return view('e-wardrobe.favorites') ;});