<?php

use App\Http\Requests\StoreSpotRequest;
use App\Models\Spot;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            // Primary key untuk tabel reviews.
            $table->id();

            // Foreign key 'spot_id' yang merujuk ke tabel 'spots'.
            // Ini menandakan review ini ditujukan untuk spot yang mana.
            $table->foreignIdFor(Spot::class);

            // Foreign key 'user_id' yang merujuk ke tabel 'users'.
            // Ini menandakan siapa yang menulis review ini.
            $table->foreignIdFor(User::class);

            // Kolom untuk isi dari ulasan, menggunakan tipe TEXT agar bisa panjang.
            $table->text('content');

            // Kolom untuk menyimpan rating, biasanya dalam bentuk angka (misal: 1-5).
            // Tipe INTEGER cocok untuk ini.
            $table->integer('rating');

            // Kolom 'created_at' dan 'updated_at' otomatis.
            $table->timestamps();

            // Menambahkan fitur soft delete untuk review.
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
