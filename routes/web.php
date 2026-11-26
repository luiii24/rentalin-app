<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HELPER DATA ARMADA (1 UNIT FISIK = 1 PLAT NOMOR UNIK)
|--------------------------------------------------------------------------
*/
$getFleetData = function () {
    $categories = collect([
        (object) ['id' => 1, 'name' => 'Mobil MPV', 'slug' => 'mobil-mpv'],
        (object) ['id' => 2, 'name' => 'City Car', 'slug' => 'city-car'],
        (object) ['id' => 3, 'name' => 'Motor Matic', 'slug' => 'motor-matic'],
    ]);

    $vehicles = collect([
        (object) [
            'id' => 1,
            'name' => 'Toyota Avanza Veloz',
            'license_plate' => 'L 1428 AB',
            'year' => 2023,
            'color' => 'Putih Mutiara',
            'transmission' => 'Automatic',
            'fuel_type' => 'Bensin',
            'spec' => 'Automatic • 7 Kursi • Bensin',
            'price_per_day' => 350000,
            'available_stock' => 1,
            'total_stock' => 1,
            'status' => 'tersedia',
            'category' => $categories[0],
            'images' => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Avanza+Veloz+L+1428+AB'],
        ],
        (object) [
            'id' => 2,
            'name' => 'Toyota Avanza Veloz',
            'license_plate' => 'L 1890 CD',
            'year' => 2024,
            'color' => 'Hitam Metalik',
            'transmission' => 'Automatic',
            'fuel_type' => 'Bensin',
            'spec' => 'Automatic • 7 Kursi • Bensin',
            'price_per_day' => 350000,
            'available_stock' => 1,
            'total_stock' => 1,
            'status' => 'tersedia',
            'category' => $categories[0],
            'images' => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Avanza+Veloz+L+1890+CD'],
        ],
        (object) [
            'id' => 3,
            'name' => 'Honda Brio RS',
            'license_plate' => 'W 1104 XY',
            'year' => 2023,
            'color' => 'Kuning Phoenix',
            'transmission' => 'Automatic',
            'fuel_type' => 'Bensin',
            'spec' => 'Automatic • 5 Kursi • Irit BBM',
            'price_per_day' => 275000,
            'available_stock' => 1,
            'total_stock' => 1,
            'status' => 'tersedia',
            'category' => $categories[1],
            'images' => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Honda+Brio+W+1104+XY'],
        ],
        (object) [
            'id' => 4,
            'name' => 'Yamaha NMAX 155 ABS',
            'license_plate' => 'L 4512 KL',
            'year' => 2024,
            'color' => 'Matte Black',
            'transmission' => 'Automatic',
            'fuel_type' => 'Bensin',
            'spec' => 'Automatic • 155cc • 2 Helm + Jas Hujan',
            'price_per_day' => 120000,
            'available_stock' => 1,
            'total_stock' => 1,
            'status' => 'tersedia',
            'category' => $categories[2],
            'images' => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Yamaha+NMAX+L+4512+KL'],
        ],
        (object) [
            'id' => 5,
            'name' => 'Mitsubishi Xpander Ultimate',
            'license_plate' => 'L 1995 MN',
            'year' => 2023,
            'color' => 'Silver Blade',
            'transmission' => 'Automatic',
            'fuel_type' => 'Bensin',
            'spec' => 'Automatic • 7 Kursi • Cruise Control',
            'price_per_day' => 425000,
            'available_stock' => 0,
            'total_stock' => 1,
            'status' => 'maintenance',
            'category' => $categories[0],
            'images' => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Xpander+L+1995+MN'],
        ],
    ]);

    return [$categories, $vehicles];
};

$makeDummyRental = function ($code, $vehicle) {
    $payment = (object) [
        'method' => 'Transfer Bank / QRIS',
        'payment_method' => 'Transfer Bank / QRIS',
        'amount' => 700000,
        'status' => 'pending',
        'payment_status' => 'pending',
        'proof_image' => null,
        'paid_at' => null,
    ];

    return (object) [
        'id' => 1,
        'booking_code' => strtoupper($code),
        'customer_name' => 'Budi Santoso',
        'customer_phone' => '081234567890',
        'customer_ktp' => '3578012345678901',
        'vehicle' => $vehicle,
        'payment' => $payment,
        'pickup_date' => now(),
        'return_date' => now()->addDays(2),
        'duration_days' => 2,
        'total_price' => 700000,
        'payment_status' => 'pending',
        'payment_proof' => null,
        'status' => 'menunggu_pembayaran',
        'notes' => 'Ambil unit di garasi utama pukul 09.00 WIB.',
        'created_at' => now(),
    ];
};

/*
|--------------------------------------------------------------------------
| HALAMAN PUBLIK (TANPA LOGIN)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/cara-pesan', function () {
    return view('how-to-order');
})->name('how-to-order');

Route::get('/cari-kendaraan', function () use ($getFleetData) {
    [$categories, $vehicles] = $getFleetData();
    return view('catalog', compact('categories', 'vehicles'));
})->name('vehicles.catalog');

Route::post('/booking', function (Request $request) {
    $code = 'RNTL-' . strtoupper(substr(md5(uniqid()), 0, 6));
    return redirect()->route('booking.invoice', $code);
})->name('booking.store');

Route::get('/nota/{code}', function ($code) use ($getFleetData, $makeDummyRental) {
    [, $vehicles] = $getFleetData();
    $rental = $makeDummyRental($code, $vehicles->first());

    return view('invoice', [
        'code' => strtoupper($code),
        'rental' => $rental,
    ]);
})->name('booking.invoice');

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
        'email' => ['required', 'email'],
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
Route::prefix('portal')->name('admin.')->group(function () use ($getFleetData, $makeDummyRental) {
    // Ringkasan Dashboard Utama Admin
    Route::get('/dashboard', function () use ($getFleetData) {
        [$categories, $vehicles] = $getFleetData();

        $stats = [
            'revenue'   => 700000,
            'rented'    => 1,
            'pending'   => 1,
            'available' => $vehicles->where('status', 'tersedia')->count(),
        ];

        return view('admin.dashboard', compact('vehicles', 'categories', 'stats'));
    })->name('dashboard');

    // Halaman Khusus Transaksi & Scan QR
    Route::get('/transactions', function () use ($getFleetData) {
        [$categories, $vehicles] = $getFleetData();
        $viewName = view()->exists('admin.transactions') ? 'admin.transactions' : 'admin.transaction';

        return view($viewName, compact('categories', 'vehicles'));
    })->name('transactions');

    // Alias Scan QR -> mengarah ke view transaksi yang sama
    Route::get('/scan', function () use ($getFleetData) {
        [$categories, $vehicles] = $getFleetData();
        $viewName = view()->exists('admin.transactions') ? 'admin.transactions' : 'admin.transaction';

        return view($viewName, compact('categories', 'vehicles'));
    })->name('scan');

    // Halaman Cetak / Detail Nota Admin
    Route::get('/invoice/{code?}', function ($code = 'RNTL-DEMO01') use ($getFleetData, $makeDummyRental) {
        [, $vehicles] = $getFleetData();
        $rental = $makeDummyRental($code, $vehicles->first());

        return view('admin.invoice', [
            'code' => strtoupper($code),
            'rental' => $rental,
        ]);
    })->name('invoice');

    // Kelola Armada (Per Unit Fisik + Plat Nomor)
    Route::get('/vehicles', function () use ($getFleetData) {
        [$categories, $vehicles] = $getFleetData();
        $viewName = view()->exists('vehicles.index') ? 'vehicles.index' : 'admin.vehicles.index';
        return view($viewName, compact('categories', 'vehicles'));
    })->name('vehicles.index');

    Route::post('/vehicles', function (Request $request) {
        return back()->with('success', 'Unit armada baru berhasil ditambahkan.');
    })->name('vehicles.store');

    Route::delete('/vehicles/{id}', function ($id) {
        return back()->with('success', 'Unit armada berhasil dihapus.');
    })->name('vehicles.destroy');
});