<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name'); // Contoh: Toyota Avanza
            $table->string('spec'); // Contoh: 7 Kursi • Manual • 2024
            $table->string('license_plate')->unique(); // Nomor Plat Utama / Kode Armada
            $table->decimal('price_per_day', 12, 2);
            $table->integer('total_stock')->default(1); // Total unit yang dimiliki
            $table->integer('available_stock')->default(1); // Sisa unit yang siap disewa
            $table->enum('status', ['tersedia', 'habis', 'maintenance'])->default('tersedia');
            $table->json('images')->nullable(); // Menyimpan array 7-8 path foto detail kendaraan
            $table->timestamps();
        });
    }

    public function down(): void
        {
        Schema::dropIfExists('vehicles');
    }
};