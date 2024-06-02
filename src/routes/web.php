<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TimeController;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SearchController;
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

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->middleware('auth')->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->intended(RouteServiceProvider::HOME);
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', '確認メールを再送信しました。');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    Route::get('/', [AuthController::class, 'index'])->middleware('verified');
    Route::post('/save', [TimeController::class, 'store'])->middleware('verified');
    Route::get('/attendance', [AttendanceController::class, 'attendance'])->middleware('verified');
    Route::get('/attendance/{date}', [AttendanceController::class, 'attendanceByDate'])->name('attendance.date')->middleware('verified');
    Route::get('/users', [AttendanceController::class, 'userList'])->name('user.list')->middleware('verified');
    Route::get('/user/{user}/attendance', [AttendanceController::class, 'userAttendance'])->name('user.attendance')->middleware('verified');
});
