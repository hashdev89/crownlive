<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// routes/web.php
use Webkul\Shop\Http\Controllers\ShopAllProductsController;

Route::get('/shop', [ShopAllProductsController::class, 'index'])->name('shop.all');

