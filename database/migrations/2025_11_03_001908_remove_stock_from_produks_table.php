<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Perintah untuk MENGHAPUS kolom 'stock'
            $table->dropColumn('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Perintah untuk MENGEMBALIKAN kolom 'stock' jika
            // Anda menjalankan 'php artisan migrate:rollback'
            // (Kita letakkan setelah kolom 'harga', sesuaikan jika perlu)
            $table->integer('stock')->default(0)->after('harga');
        });
    }
};
