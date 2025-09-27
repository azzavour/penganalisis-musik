<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\MusicManagerController;

// Arahkan halaman utama langsung ke Music Manager
Route::get('/', [MusicManagerController::class, 'index'])->name('music-manager.index');

// Anda bisa menambahkan rute untuk upload nanti jika diperlukan
// Route::post('/upload', [MusicManagerController::class, 'upload'])->name('music-manager.upload');