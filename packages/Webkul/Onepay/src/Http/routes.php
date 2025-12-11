<?php

use Illuminate\Support\Facades\Route;
use Webkul\Onepay\Http\Controllers\OnepayController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/onepay/redirect', [OnepayController::class, 'redirect'])->name('onepay.redirect');
    Route::get('/onepay/callback', [OnepayController::class, 'callback'])->name('onepay.callback');
    Route::post('/onepay/callback', [OnepayController::class, 'callback'])->name('onepay.callback');
});

