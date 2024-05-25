<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TimeController;

// use App\Http\Controllers\TimeController;

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

Route::middleware('auth')->group(function () {
        Route::get('/', [AuthController::class, 'index']);
        Route::post('/save', [TimeController::class, 'store']);
        Route::get('/attendance', [TimeController::class,'attendance']);
        Route::get('/attendance/{date}', [TimeController::class, 'attendanceByDate'])->name('attendance.date');

        Route::get('/email/verify', function () {
        return view('auth.verify-email');
        })->middleware('auth')->name('verification.notice');

        Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
            $request->fulfill();

            return redirect('/login');
        })->middleware(['auth', 'signed'])->name('verification.verify');

        Route::post('/email/verification-notification', function (Request $request) {
            $request->user()->sendEmailVerificationNotification();

            return back()->with('message', 'Verification link sent!');
        })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
        });
