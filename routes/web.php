<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDepositController;
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
Route::resource("user.deposit", UserDepositController::class);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


