<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;

// Main routes
Route::get('/', function () {
    return view('admin.dashboard');
});

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('dashboard');

Route::get('/leaderboard', function () {
    return view('leaderboard');
})->name('leaderboard');

// Admin config routes
Route::get('/admin/config', function () {
    return view('admin.config.index');
})->name('admin.config');

Route::get('/admin/config/edit', function () {
    return view('admin.config.edit');
})->name('admin.config.edit');

Route::get('/admin/config/sync', function () {
    return view('admin.config.sync');
})->name('admin.config.sync');

// Admin content management routes
Route::get('/admin/content/scenarios', function () {
    return view('admin.content.scenarios');
})->name('admin.content.scenarios');

Route::get('/admin/content/cards', function () {
    return view('admin.content.cards');
})->name('admin.content.cards');

Route::get('/admin/content/quiz', function () {
    return view('admin.content.quiz');
})->name('admin.content.quiz');

// Admin login routes
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');