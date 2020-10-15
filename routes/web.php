<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDepositController;
use App\Http\Controllers\UserDepositStoreController;
use App\Http\Controllers\UserHomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Auth::routes();

Route::get('/', function () {
    return redirect()->route("user.index");
});

Route::resource("user", UserController::class);
Route::get("user/{user}/home", UserHomeController::class)->name("user.home");
Route::post("user/{user}/deposit/store", UserDepositStoreController::class)->name("user.deposit.store");

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


