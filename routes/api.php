<?php

use App\Http\Controllers\EmailOtpController;
use App\Http\Controllers\MGeneralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::middleware('api')->namespace('Api')->group(function () {

    Route::prefix('m')->group(function () {

        Route::post('login', 'AuthController@login');

        Route::post('forgot-password', 'AuthController@forgotPassword');

        Route::post('otp-create', [EmailOtpController::class, 'create']);


        Route::middleware('auth:sanctum')->group(function () {

            Route::post('otp-send', [EmailOtpController::class, 'send']);

            Route::post('logout', 'AuthController@logout');


            Route::controller('GeneralController')->group(function () {

                Route::get('profile', 'profileIndex');

                Route::post('profile', 'profileStore');

                Route::post('profile-picture', 'updateProfilePicture');

                Route::get('rankings', 'rankingIndex');

                Route::get('roles', 'roleIndex');

                Route::get('wallets', 'walletIndex');

                Route::post('wallets/{wallet}', 'walletUpdate');

                Route::post('set-security-pin', 'setSecurityPin');

                Route::post('change-security-pin', 'changeSecurityPin');

                Route::get('users', 'userIndex');

                Route::post('users', 'userStore');

                Route::delete('users/{user}', 'userDestroy');

                Route::get('roles/{role}/permissions', 'rolePermissionIndex');

                Route::post('roles/{role}/permissions', 'rolePermissionStore');

                Route::post('deposit', 'deposit');

                Route::post('withdrawal', 'withdrawal');

                Route::get('transactions', 'transactionIndex');

                Route::get('transactions/{transaction}', 'transactionShow');

                Route::post('settings', 'userSettingStore');
            });
        });
    });

    Route::post('deposit', 'ThirdPartyController@deposit');
});
