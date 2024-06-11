<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GajiController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\MADController;
use App\Http\Controllers\PenempatanController;
use App\Http\Controllers\PosisiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', [AuthController::class,'index'])->name('login');
Route::post('/login', [AuthController::class,'login']);
Route::post('/logout', [AuthController::class,'logout'])->name('logout');


Route::get('/login', [AuthController::class,'index'])->name('login');

Route::middleware('auth')->group(function () {

Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');
Route::get('/user', [UserController::class,'index'])->name('user');
Route::get('/user/create', [UserController::class,'create'])->name('user.create');
Route::post('/user/store', [UserController::class,'store'])->name('user.store');
Route::get('/user/show/{id}', [UserController::class,'show'])->name('showuser');
Route::post('/user/update/{id}', [UserController::class,'update'])->name('updateuser');
Route::delete('/deleteuser/{id}', [UserController::class, 'destroy'])->name('deleteuser');

Route::get('/penempatan', [PenempatanController::class,'index'])->name('penempatan');
Route::get('/penempatan/create', [PenempatanController::class,'create'])->name('penempatan.create');
Route::get('/downloadpenempatan', [PenempatanController::class,'download'])->name('downloadpenempatan');
Route::post('/penempatan/store', [PenempatanController::class,'store'])->name('penempatan.store');
Route::post('/importpenempatan', [PenempatanController::class,'import'])->name('importpenempatan');
Route::get('/penempatan/show/{id}', [PenempatanController::class,'show'])->name('showpenempatan');
Route::post('/penempatan/update/{id}', [PenempatanController::class,'update'])->name('updatepenempatan');
Route::delete('/deletepenempatan/{id}', [PenempatanController::class, 'destroy'])->name('deletepenempatan');



Route::get('/posisi', [PosisiController::class,'index'])->name('posisi');
Route::get('/posisi/create', [PosisiController::class,'create'])->name('posisi.create');
Route::get('/downloadposisi', [PosisiController::class,'download'])->name('downloadposisi');
Route::post('/posisi/store', [PosisiController::class,'store'])->name('posisi.store');
Route::post('/importposisi', [PosisiController::class,'import'])->name('importposisi');
Route::get('/posisi/show/{id}', [PosisiController::class,'show'])->name('showposisi');
Route::post('/posisi/update/{id}', [PosisiController::class,'update'])->name('updateposisi');
Route::delete('/deleteposisi/{id}', [PosisiController::class, 'destroy'])->name('deleteposisi');


Route::get('/karyawan', [KaryawanController::class,'index'])->name('karyawan');
Route::get('/karyawan/create', [KaryawanController::class,'create'])->name('karyawan.create');
Route::post('/karyawan/store', [KaryawanController::class,'store'])->name('karyawan.store');
Route::get('/downloadkaryawan', [KaryawanController::class,'download'])->name('downloadkaryawan');
Route::post('/importkaryawan', [KaryawanController::class,'import'])->name('importkaryawan');
Route::get('/karyawan/show/{id}', [KaryawanController::class,'show'])->name('showkaryawan');
Route::post('/karyawan/update/{id}', [KaryawanController::class,'update'])->name('updatekaryawan');
Route::delete('/deletekaryawan/{id}', [KaryawanController::class, 'destroy'])->name('deletekaryawan');


Route::get('/mad', [MADController::class,'index'])->name('mad');
Route::get('/mad/create', [MADController::class,'create'])->name('mad.create');
Route::post('/mad/store', [MADController::class,'store'])->name('mad.store');
Route::get('/downloadmad', [MADController::class,'download'])->name('downloadmad');
Route::post('/importmad', [MADController::class,'import'])->name('importmad');
Route::get('/mad/show/{id}', [MADController::class,'show'])->name('showmad');
Route::post('/mad/update/{id}', [MADController::class,'update'])->name('updatemad');
Route::delete('/deletemad/{id}', [MADController::class, 'destroy'])->name('deletemad');
Route::get('/exportmad', [MadController::class, 'exportMad'])->name('exportmad');


Route::get('/holiday', [HolidayController::class,'index'])->name('holiday');
Route::get('/holiday/show/{id}', [HolidayController::class,'show'])->name('showholiday');
Route::get('/holiday/create', [HolidayController::class,'create'])->name('holiday.create');
Route::post('/holiday/store', [HolidayController::class,'store'])->name('holiday.store');
Route::post('/holiday/update/{id}', [HolidayController::class,'update'])->name('updateholiday');

Route::get('/changepassword', [AuthController::class,'showChangePasswordForm'])->name('password');
Route::post('/change', [AuthController::class,'changePassword'])->name('change-password');

Route::post('/user/{user}/reset-password', [UserController::class,'resetPassword'])->name('reset-password');

Route::get('/gaji', [GajiController::class,'index'])->name('gaji');
Route::get('/gaji/create', [GajiController::class,'create'])->name('gaji.create');
Route::post('/gaji/store', [GajiController::class,'store'])->name('gaji.store');
Route::get('/gaji/show/{id}', [GajiController::class,'show'])->name('showgaji');
Route::post('/gaji/update/{id}', [GajiController::class,'update'])->name('updategaji');
Route::delete('/deletegaji/{id}', [GajiController::class, 'destroy'])->name('deletegaji');
Route::get('/downloadgaji', [GajiController::class,'download'])->name('downloadgaji');
Route::post('/importgaji', [GajiController::class,'import'])->name('importgaji');



});