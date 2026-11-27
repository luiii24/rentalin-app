<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@rentalin.id'],
            [
                'name'     => 'Administrator',
                'phone'    => '081234567890',
                'role'     => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        $mpv   = Category::updateOrCreate(['slug' => 'mobil-mpv'], ['name' => 'Mobil MPV']);
        $city  = Category::updateOrCreate(['slug' => 'city-car'], ['name' => 'City Car']);
        $matic = Category::updateOrCreate(['slug' => 'motor-matic'], ['name' => 'Motor Matic']);

        $avanza1 = Vehicle::create([
            'category_id'     => $mpv->id,
            'name'            => 'Toyota Avanza Veloz',
            'license_plate'   => 'L 1428 AB',
            'spec'            => 'Automatic • 7 Kursi • Bensin | Putih Mutiara (2023)',
            'price_per_day'   => 350000,
            'total_stock'     => 1,
            'available_stock' => 1,
            'status'          => 'tersedia',
            'images'          => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Avanza+Veloz+L+1428+AB'],
        ]);

        Vehicle::create([
            'category_id'     => $mpv->id,
            'name'            => 'Toyota Avanza Veloz',
            'license_plate'   => 'L 1890 CD',
            'spec'            => 'Automatic • 7 Kursi • Bensin | Hitam Metalik (2024)',
            'price_per_day'   => 350000,
            'total_stock'     => 1,
            'available_stock' => 1,
            'status'          => 'tersedia',
            'images'          => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Avanza+Veloz+L+1890+CD'],
        ]);

        $brio = Vehicle::create([
            'category_id'     => $city->id,
            'name'            => 'Honda Brio RS',
            'license_plate'   => 'W 1104 XY',
            'spec'            => 'Automatic • 5 Kursi • Irit BBM | Kuning Phoenix (2023)',
            'price_per_day'   => 275000,
            'total_stock'     => 1,
            'available_stock' => 1,
            'status'          => 'tersedia',
            'images'          => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Honda+Brio+W+1104+XY'],
        ]);

        Vehicle::create([
            'category_id'     => $matic->id,
            'name'            => 'Yamaha NMAX 155 ABS',
            'license_plate'   => 'L 4512 KL',
            'spec'            => 'Automatic • 155cc • 2 Helm + Jas Hujan | Matte Black (2024)',
            'price_per_day'   => 120000,
            'total_stock'     => 1,
            'available_stock' => 1,
            'status'          => 'tersedia',
            'images'          => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Yamaha+NMAX+L+4512+KL'],
        ]);

        Vehicle::create([
            'category_id'     => $mpv->id,
            'name'            => 'Mitsubishi Xpander Ultimate',
            'license_plate'   => 'L 1995 MN',
            'spec'            => 'Automatic • 7 Kursi • Cruise Control | Silver Blade (2023)',
            'price_per_day'   => 425000,
            'total_stock'     => 1,
            'available_stock' => 0,
            'status'          => 'maintenance',
            'images'          => ['https://placehold.co/600x450/f3f4f6/1e1e1e?text=Xpander+L+1995+MN'],
        ]);

        // Contoh Transaksi 1: Menunggu Konfirmasi QRIS Semi-Manual
        $rental1 = Rental::create([
            'booking_code'   => 'RNTL-DEMO01',
            'vehicle_id'     => $avanza1->id,
            'customer_name'  => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'pickup_date'    => now(),
            'duration_days'  => 2,
            'total_price'    => 700000,
            'ktp_verified'   => false,
            'status'         => 'pending',
        ]);

        Payment::create([
            'rental_id'      => $rental1->id,
            'payment_method' => 'QRIS',
            'amount'         => 700000,
            'payment_status' => 'unpaid',
        ]);

        // Contoh Transaksi 2: Menunggu Pembayaran
        $rental2 = Rental::create([
            'booking_code'   => 'RNTL-DEMO02',
            'vehicle_id'     => $brio->id,
            'customer_name'  => 'Siti Aminah',
            'customer_phone' => '085712349876',
            'pickup_date'    => now()->addHours(2),
            'duration_days'  => 1,
            'total_price'    => 275000,
            'ktp_verified'   => false,
            'status'         => 'pending',
        ]);

        Payment::create([
            'rental_id'      => $rental2->id,
            'payment_method' => 'QRIS',
            'amount'         => 275000,
            'payment_status' => 'unpaid',
        ]);
    }
}