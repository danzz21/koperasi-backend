<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('jumlah', 15, 2);
            $table->integer('tenor'); // dalam bulan
            $table->decimal('bunga', 5, 2)->default(1.5); // % per bulan
            $table->decimal('angsuran_per_bulan', 15, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'lunas'])->default('pending');
            $table->string('tujuan')->nullable();
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_approved')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
};
