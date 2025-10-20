<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\passwordController;
use App\Http\Controllers\PegawaiController;
use Illuminate\Support\Facades\Route;

//  Redirect root ke login
Route::get('/', fn() => redirect()->route('login.index'));

//  Login & Logout
Route::get('/login', [LoginController::class, 'index'])->name('login.index');
Route::post('/login', [LoginController::class, 'store'])->name('login.proses');
Route::get('/logout', [LoginController::class, 'logout'])->name('login.logout');

// Lupa password & verifikasi wajah
Route::get('/lupa-password', [LoginController::class, 'lupaPassword'])->name('password.lupa');
Route::post('/password/checkAdmin', [LoginController::class, 'checkAdmin'])->name('password.checkAdmin');
Route::post('/password/get-descriptor', [LoginController::class, 'getDescriptor'])->name('password.getDescriptor');
Route::post('/password/verify', [LoginController::class, 'verifikasiWajah'])->name('password.verifikasiWajah');
Route::post('/password/reset', [LoginController::class, 'resetPassword'])->name('password.reset');

Route::middleware('cekLogin')->group(function () {

    // Home
    Route::get('/home', [HomeController::class, 'index'])->name('home.index');

    //  Pegawai
    Route::prefix('pegawai')->group(function () {
        Route::get('/', [PegawaiController::class, 'index'])->name('pegawai.index');
        Route::get('create', [PegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('store', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('{nip}/edit', [PegawaiController::class, 'edit'])->name('pegawai.edit');
        Route::put('{nip}', [PegawaiController::class, 'update'])->name('pegawai.update');
        Route::delete('{nip}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');
        Route::get('searchByName', [PegawaiController::class, 'searchByName'])->name('pegawai.searchByName');
        Route::get('searchByNip', [PegawaiController::class, 'searchByNip'])->name('pegawai.searchByNip');
        Route::get('print', [PegawaiController::class, 'print'])->name('pegawai.print');
        Route::post('check-nip', [PegawaiController::class, 'checkNip'])->name('pegawai.checkNip');
        Route::get('{nip}/data-wajah', [PegawaiController::class, 'datawajah'])->name('pegawai.data-wajah');
        Route::put('{nip}/data-wajah', [PegawaiController::class, 'createDataWajah'])->name('pegawai.createDataWajah');
        Route::get('/pegawai/barcode/{nip}', [PegawaiController::class, 'downloadBarcode'])->name('pegawai.barcode');
        Route::get('{nip}/rekam-wajah', [PegawaiController::class, 'rekamWajah'])->name('pegawai.rekamWajah');
        Route::post('{nip}/simpan-wajah', [PegawaiController::class, 'simpanWajah'])->name('pegawai.simpanWajah');
        Route::delete('/pegawai/hapus-wajah/{nip}', [PegawaiController::class, 'hapusWajah'])->name('pegawai.hapus-wajah');
    });


    //  Admin
    Route::prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('create', [AdminController::class, 'create'])->name('admin.create');
        Route::post('store', [AdminController::class, 'store'])->name('admin.store');
        Route::get('{id}/edit', [AdminController::class, 'edit'])->name('admin.edit');
        Route::put('{id}', [AdminController::class, 'update'])->name('admin.update');
        Route::delete('{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
    });

    //  Absensi
    Route::prefix('absensi')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('search', [AbsensiController::class, 'search'])->name('absensi.search');
        Route::get('kamera', [AbsensiController::class, 'kamera'])->name('absensi.kamera');
        Route::get('/get-descriptors', [AbsensiController::class, 'getDescriptors'])->name('absensi.getDescriptors');
        Route::post('/proses', [AbsensiController::class, 'proses'])->name('absensi.proses');
        Route::get('/detail/{kegiatan_id}', [AbsensiController::class, 'detailKegiatan'])->name('absensi.detailKegiatan');
        Route::get('/manual/{id}', [AbsensiController::class, 'manual'])->name('absensi.manual');
        Route::post('/manual/simpan', [AbsensiController::class, 'simpanManual'])->name('absensi.manual.simpan');
        Route::delete('/{id}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');
        Route::get('/{id}/edit', [AbsensiController::class, 'edit'])->name('absensi.edit');
        Route::put('/{id}', [AbsensiController::class, 'update'])->name('absensi.update');
        Route::delete('/absensi/hapus-kegiatan/{kegiatan_id}', [AbsensiController::class, 'destroyKegiatan'])->name('absensi.destroyKegiatan');
        Route::delete('/absensi/hapus/{id}', [AbsensiController::class, 'destroyOne'])->name('absensi.destroyOne');
        Route::post('/absensi/proses-aksi', [AbsensiController::class, 'prosesAbsensi'])->name('absensi.proses-aksi');
        Route::get('/absensi/cetak/{kegiatan_id}', [AbsensiController::class, 'cetakDetail'])->name('absensi.cetak');
        Route::get('/kamera-aksi', [AbsensiController::class, 'kameraAksi'])->name('absensi.kameraAksi');
    });

    //  Password
    Route::prefix('password')->group(function () {    
        Route::get('/password', [PasswordController::class, 'index'])->name('password.index');
        Route::post('/password/update', [PasswordController::class, 'update'])->name('password.update');

    });

    
});
