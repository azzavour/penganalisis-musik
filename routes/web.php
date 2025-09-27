<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\MusicManagerController;
use App\Http\Controllers\Web\MusicImportController;
use App\Http\Controllers\ProfileController;

Route::middleware('auth')->group(function () {
    Route::get('/', [MusicManagerController::class, 'index'])->name('music-manager.index');
    Route::get('/music-manager/create', [MusicManagerController::class, 'create'])->name('music-manager.create');
    Route::post('music-manager/preview', [MusicImportController::class, 'preview'])->name('music-manager.preview');
    Route::post('music-manager/import', [MusicImportController::class, 'import'])->name('music-manager.import');
    Route::get('/music-manager/export', [MusicManagerController::class, 'export'])->name('music-manager.export');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
