<?php

use App\Http\Controllers\EmailOtpController;
use App\Http\Controllers\PaymentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::namespace("Web")->group(function () {

    Route::middleware('guest')->group(function () {

        Route::redirect('/', 'login');

        Route::get('login', function () {
            return Inertia::render('Auth/Login');
        })->name('login');
        Route::post('login', 'AuthController@login');

        Route::post('forgot-password', 'AuthController@forgotPassword');

        Route::post('otp-create', [EmailOtpController::class, 'create']);

        Route::post('receiveDeposit', [PaymentController::class, 'deposit']);
        Route::post('receiveWithdrawal', [PaymentController::class, 'withdrawal']);
        Route::post('updateTransaction', [PaymentController::class, 'updateTransaction']);
    });

    Route::middleware('auth')->group(function () {

        Route::post('otp-send', [EmailOtpController::class, 'send']);

        Route::post('logout',  'AuthController@logout');

        Route::resource('merchants', 'MerchantController');

        Route::resource('transactions', 'TransactionController');

        Route::resource('rankings', 'RankingController');

        Route::resource('roles', 'RoleController');

        Route::resource('users', 'UserController');

        Route::resource('permissiongroups', 'PermissionGroupController');

        Route::resource('permissions', 'PermissionController');

        Route::resource('activitylogs', 'ActivityLogController');

        Route::resource('merchants.wallets', 'MerchantWalletController');

        Route::resource('roles.permissions', 'RolePermissionController');

        Route::resource('merchants.users', 'MerchantUserController');

        Route::resource('merchants.roles', 'MerchantRoleController');

        Route::resource('merchants.roles.permissions', 'MerchantRolePermissionController');

        Route::resource('merchants.transactions', 'MerchantTransactionController');

        Route::get('merchant-roles/{merchant?}', 'GeneralController@getRolesByMerchant');



        Route::prefix('table')->controller('TableController')->group(function () {
            Route::get('merchants', 'MerchantTable');
            Route::get('transactions', 'TransactionTable');
            Route::get('rankings', 'RankingTable');
            Route::get('roles', 'RoleTable');
            Route::get('users', 'UserTable');
            Route::get('permissiongroups', 'PermissionGroupTable');
            Route::get('permissions', 'PermissionTable');
            Route::get('activitylogs', 'ActivityLogTable');
            Route::get('merchants/{merchant}/wallets', 'MerchantWalletTable');
            Route::get('merchants/{merchant}/users', 'MerchantUserTable');
            Route::get('merchants/{merchant}/roles', 'MerchantRoleTable');
            Route::get('merchants/{merchant}/transactions', 'MerchantTransactionTable');
        });
    });
});
