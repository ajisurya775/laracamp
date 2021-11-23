<?php

use Illuminate\Support\Facades\Route;
use App\http\Controllers\UserController;
use App\http\Controllers\User\CheckoutController;
use App\http\Controllers\HomeController;

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

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

//socialite
Route::get('sign-in-google', [UserController::class,'google'])->name('user.login.google');
Route::get('auth/google/callback', [UserController::class,'handleProviderCallback'])->name('user.google.callback');



Route::middleware(['auth'])->group(function(){
    //checkout route
    Route::get('checkout/success', [CheckoutController::class,'success'])->name('checkout.success');
    Route::get('checkout/{camp:slug}', [CheckoutController::class,'create'])->name('checkout.create');
    Route::post('checkout/{camp}', [CheckoutController::class,'store'])->name('checkout.store');
    
    //user dashboard route
    Route::get('dashboard', [HomeController::class,'dashboard'])->name('dashboard');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
