<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\MusicManagerController;
use App\Http\Controllers\Web\MusicImportController;

// Arahkan halaman utama langsung ke Music Manager
Route::get('/', [MusicManagerController::class, 'index'])->name('music-manager.index');
Route::post('music-manager/preview', [MusicImportController::class, 'preview'])->name('music-manager.preview');
Route::post('music-manager/import', [MusicImportController::class, 'import'])->name('music-manager.import');