<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlannerController;

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::post('/', [HomeController::class, 'post'])->name('home.post');

Route::get('/planner', [PlannerController::class, 'index'])->name('planner.index');
