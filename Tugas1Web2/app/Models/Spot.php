<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;

class Spot extends Model
{
    public function up(): void
    {
        Schema::create('spots', function (Blueprint $table) {
            // $table->id() membuat kolom 'id' sebagai primary key (auto-incrementing BigInt).
            $table->id();

            // $table->foreignIdFor(User::class) adalah shortcut Laravel untuk membuat
            // kolom foreign key 'user_id' (unsignedBigInteger). Kolom ini akan merujuk
            // ke 'id' pada tabel 'users'. Ini menandakan spot ini dibuat oleh user siapa.
            $table->foreignIdFor(User::class);

            // $table->string('name') membuat kolom 'name' dengan tipe data VARCHAR.
            $table->string('name');

            // $table->string('address') membuat kolom 'address' dengan tipe data VARCHAR.
            $table->string('address');

            // $table->text('picture') membuat kolom 'picture' dengan tipe data TEXT.
            // Tipe TEXT cocok untuk menyimpan data teks yang panjang, seperti path atau URL gambar.
            $table->text('picture');

            // $table->timestamps() membuat dua kolom: 'created_at' dan 'updated_at'.
            // Laravel akan secara otomatis mengisi dan memperbarui kolom ini.
            $table->timestamps();

            // $table->softDeletes() menambahkan kolom 'deleted_at'.
            // Ini mengaktifkan fitur "soft delete", di mana data tidak benar-benar dihapus
            // dari database, melainkan hanya ditandai sebagai telah dihapus.
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spots');
    }
}
