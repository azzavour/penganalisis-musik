<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\MusicManagerController;

// Default Laravel route
Route::get('/', function () {
    return view('welcome');
});

// Music Manager routes
Route::prefix('music-manager')->name('music-manager.')->group(function () {
    Route::get('/', [MusicManagerController::class, 'index'])->name('index');
    Route::get('/data', [MusicManagerController::class, 'getData'])->name('data');
    Route::post('/preview-upload', [MusicManagerController::class, 'previewUpload'])->name('preview-upload');
    Route::post('/upload', [MusicManagerController::class, 'uploadData'])->name('upload');
});