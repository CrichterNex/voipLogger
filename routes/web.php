<?php

use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->middleware('auth')->name('root');

Auth::routes(['register' => false, 'reset' => false, 'verify' => false, 'remember_me' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth');
Route::get('/search', [SearchController::class, 'index'])->name('search.index')->middleware('auth');
Route::post('/search', [SearchController::class, 'search'])->name('search')->middleware('auth');
Route::post('/export', [SearchController::class, 'export'])->name('export')->middleware('auth');
Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index')->middleware('auth');
Route::post('/settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.store')->middleware('auth');
