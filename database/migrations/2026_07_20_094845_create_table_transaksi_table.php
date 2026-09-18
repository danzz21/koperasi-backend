<?php
// database/migrations/xxxx_create_transaksi_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('jenis', ['simpanan', 'pinjaman', 'angsuran', 'ppob']);
            $table->enum('tipe', ['kredit', 'debet']);
            $table->decimal('jumlah', 15, 2);
            $table->string('keterangan')->nullable();
            $table->string('referensi')->nullable(); // ID dari tabel terkait
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
