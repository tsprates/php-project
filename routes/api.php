<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(
    [
        'middleware' => 'api',
        'prefix' => 'auth',
    ],
    function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    }
);

Route::group(
    [
        'middleware' => 'api',
        'prefix' => 'accounts',
    ],
    function () {
        Route::post('balance', [AccountController::class, 'getBalance']);
        Route::post('deposit', [AccountController::class, 'deposit']);
        Route::post('withdraw', [AccountController::class, 'withdraw']);
        Route::post('transfer', [AccountController::class, 'transfer']);
        Route::post('transactions', [AccountController::class, 'getTransactions']);
    }
);
