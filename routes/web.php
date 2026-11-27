<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\PublicController;

use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| HALAMAN PUBLIK (TANPA LOGIN - DATABASE ASLI)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/cara-pesan', [PublicController::class, 'howToOrder'])->name('how-to-order');
Route::get('/cari-kendaraan', [PublicController::class, 'catalog'])->name('vehicles.catalog');

Route::post('/booking', [PublicController::class, 'storeBooking'])->name('booking.store');
Route::get('/nota/{code}', [PublicController::class, 'invoice'])->name('booking.invoice');
Route::post('/nota/{code}/upload-proof', [PublicController::class, 'uploadPaymentProof'])->name('portal.payment.upload');
Route::get('/cek-nota', [PublicController::class, 'checkBooking'])->name('booking.check');

/*
|--------------------------------------------------------------------------
| AUTENTIKASI ADMIN (/login & /portal/login)
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return view('auth.login');
})->name('login');

Route::get('/portal/login', function () {
    return redirect()->route('login');
});

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    return back()->withErrors([
        'email' => 'Email atau kata sandi admin tidak sesuai.',
    ])->onlyInput('email');
})->name('login.post');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| PORTAL ADMIN (/portal/...)
|--------------------------------------------------------------------------
*/
Route::prefix('portal')->name('admin.')->middleware('auth')->group(function () {
    // Ringkasan Dashboard Utama & Upload QRIS Toko
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/settings/qris', [DashboardController::class, 'uploadQris'])->name('qris.upload');
    Route::post('/settings/qris/reset', [DashboardController::class, 'resetQris'])->name('qris.reset');

    // Transaksi, Verifikasi QRIS Semi-Manual, Serah Terima & Pengembalian Unit
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('/scan', [TransactionController::class, 'index'])->name('scan');
    Route::post('/transactions/{code}/confirm-payment', [TransactionController::class, 'confirmPayment'])->name('transactions.confirm-payment');
    Route::post('/transactions/{code}/confirm-pickup', [TransactionController::class, 'confirmPickup'])->name('transactions.confirm-pickup');
    Route::post('/transactions/{code}/confirm-return', [TransactionController::class, 'confirmReturn'])->name('transactions.confirm-return');

    // Halaman Cetak / Detail Nota Admin
    Route::get('/invoice/{code?}', [PublicController::class, 'invoice'])->name('invoice');

    // Kelola Armada & Kategori (Database Asli)
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    Route::post('/categories', [VehicleController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [VehicleController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [VehicleController::class, 'destroyCategory'])->name('categories.destroy');
});