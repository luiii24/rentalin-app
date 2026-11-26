<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique(); // Kode unik untuk Barcode & Nota
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete(); // Diisi saat kasir memproses nota
            
            // Data Guest Checkout (Tanpa Login)
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->dateTime('pickup_date');
            $table->integer('duration_days');
            $table->decimal('total_price', 12, 2);
            
            // Validasi & Status Transaksi
            $table->boolean('ktp_verified')->default(false);
            $table->enum('status', ['pending', 'active', 'completed', 'canceled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};