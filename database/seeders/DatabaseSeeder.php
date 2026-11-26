<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin & Kasir Default
        User::create([
            'name' => 'Admin Pusat',
            'email' => 'admin@rentalin.com',
            'phone' => '081234567890',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Kasir Loket 1',
            'email' => 'kasir@rentalin.com',
            'phone' => '081298765432',
            'role' => 'kasir',
            'password' => Hash::make('password123'),
        ]);

        // 2. Buat Kategori Utama (Mobil & Motor)
        $mobil = Category::create([
            'name' => 'Mobil',
            'slug' => 'mobil',
        ]);

        $motor = Category::create([
            'name' => 'Motor',
            'slug' => 'motor',
        ]);

        // 3. Buat Data Armada Awal (Stok Unit + Galeri Multi-Foto)
        Vehicle::create([
            'category_id' => $mobil->id,
            'name' => 'Toyota Avanza',
            'spec' => '7 Kursi • Manual • 2024',
            'license_plate' => 'L 1024 AA',
            'price_per_day' => 350000,
            'total_stock' => 3,
            'available_stock' => 3,
            'status' => 'tersedia',
            'images' => [
                'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=600&q=80',
            ],
        ]);

        Vehicle::create([
            'category_id' => $mobil->id,
            'name' => 'Honda Brio',
            'spec' => '5 Kursi • Automatic • 2023',
            'license_plate' => 'L 1882 BB',
            'price_per_day' => 300000,
            'total_stock' => 2,
            'available_stock' => 2,
            'status' => 'tersedia',
            'images' => [
                'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=600&q=80',
            ],
        ]);

        Vehicle::create([
            'category_id' => $motor->id,
            'name' => 'Honda Vario 160',
            'spec' => '160cc • Automatic • 2024',
            'license_plate' => 'L 4512 CC',
            'price_per_day' => 100000,
            'total_stock' => 5,
            'available_stock' => 5,
            'status' => 'tersedia',
            'images' => [
                'https://images.unsplash.com/photo-1629239851605-64906f254b9d?auto=format&fit=crop&w=600&q=80',
            ],
        ]);

        Vehicle::create([
            'category_id' => $motor->id,
            'name' => 'Yamaha NMAX',
            'spec' => '155cc • Automatic • 2024',
            'license_plate' => 'L 5590 DD',
            'price_per_day' => 150000,
            'total_stock' => 4,
            'available_stock' => 0,
            'status' => 'habis', // Contoh unit yang seluruh stoknya sedang disewa
            'images' => [
                'https://images.unsplash.com/photo-1558981359-219d6364c9c8?auto=format&fit=crop&w=600&q=80',
            ],
        ]);
    }
}