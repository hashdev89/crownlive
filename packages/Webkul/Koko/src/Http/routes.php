<?php

use Illuminate\Support\Facades\Route;
use Webkul\Koko\Http\Controllers\KokoController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/koko/redirect', [KokoController::class, 'redirect'])->name('koko.redirect');
    Route::get('/koko/success', [KokoController::class, 'success'])->name('koko.success');
    Route::get('/koko/cancel', [KokoController::class, 'cancel'])->name('koko.cancel');
    Route::post('/koko/response', [KokoController::class, 'response'])->name('koko.response');
});

