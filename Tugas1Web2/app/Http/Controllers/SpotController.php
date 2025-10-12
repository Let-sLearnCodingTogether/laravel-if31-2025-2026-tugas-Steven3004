<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreSpotRequest;
use App\Http\Requests\UpdateSpotRequest;
use App\Models\Spot;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SpotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // yang mungkin terjadi selama proses eksekusi kode.
        try {
            // Memulai query untuk mengambil data dari model Spot.
            $spots = Spot ::with([
                // 'with' digunakan untuk Eager Loading. Ini sangat penting untuk performa
                // karena menghindari masalah N+1 query. Kita memuat relasi 'categories' dan 'user'.
                // 'categories:category,spot_id' berarti kita hanya mengambil kolom 'category' dan 'spot_id' dari tabel categories.
                'categories:category,spot_id',
                // Sama seperti di atas, kita hanya mengambil kolom 'id' dan 'name' dari tabel user yang berelasi.
                'user:id,name'
            ])
                // 'withCount' akan menghitung jumlah data pada relasi 'reviews' dan menyimpannya
                // dalam properti baru bernama 'reviews_count' pada setiap objek spot.
                ->withCount(['reviews'])
                // 'withSum' akan menjumlahkan nilai dari kolom 'rating' pada relasi 'reviews'.
                // Hasilnya akan disimpan dalam properti 'reviews_sum_rating'.
                ->withSum('reviews', 'rating')
                // Mengurutkan hasil berdasarkan kolom 'created_at' dari yang terbaru (descending).
                ->orderBy('created_at', 'desc')
                // 'paginate' akan membagi hasil query menjadi beberapa halaman.
                // request('size', 10) berarti kita mengambil 'size' dari query parameter URL (?size=5),
                // jika tidak ada, defaultnya adalah 10 item per halaman.
                ->paginate(request('size', 10));

            // Mengembalikan respons dalam format JSON dengan data spots yang berhasil diambil.
            // Status code 200 (OK) menandakan request berhasil.
            return Response::json([
                'message' => "Spots retrieved successfully",
                'data' => $spots
            ], 200);
        } catch (Exception $e) {
            // Jika terjadi error di dalam blok 'try', eksekusi akan lompat ke blok 'catch'.
            // Kita mengembalikan respons JSON yang berisi pesan error.
            // Status code 500 (Internal Server Error) menandakan ada masalah di server.
            return Response::json([
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    // ... method lainnya

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpotRequest $request)
    {
        try {
            // $request->safe()->all() akan mengambil semua data yang TELAH LOLOS validasi
            // yang didefinisikan di dalam class StoreSpotRequest. Ini lebih aman daripada $request->all().
            $validated = $request->safe()->all();

            // Meng-handle upload file. File 'picture' dari request akan disimpan
            // di dalam 'public/spots'. Path filenya akan disimpan di variabel $picture_path.
            $picture_path = Storage::disk('public')->putFile('spots', $request->file('picture'));

            // Membuat record baru di tabel 'spots' menggunakan metode 'create'.
            $spot = Spot::create([
                // Mengambil id dari user yang sedang login (terautentikasi).
                'user_id' => Auth::user()->id,
                // Mengambil 'name' dari data yang sudah divalidasi.
                'name' => $validated['name'],
                // Mengambil 'address' dari data yang sudah divalidasi.
                'address' => $validated['address'],
                // Menyimpan path gambar yang sudah di-upload.
                'picture' => $picture_path,
            ]);

            // Memeriksa apakah spot berhasil dibuat.
            if ($spot) {
                $categories = [];

                // Looping melalui array 'category' yang dikirim dari request.
                foreach ($validated['category'] as $category) {
                    // Menyiapkan array data untuk dimasukkan ke tabel 'categories'.
                    $categories[] = [
                        'spot_id' => $spot->id, // ID dari spot yang baru saja dibuat.
                        'category' => $category,
                        'created_at' => now(), // Menambahkan timestamp manual
                        'updated_at' => now(), // karena metode 'insert' tidak mengisinya otomatis.
                    ];
                }

                // Menggunakan 'insert' untuk melakukan bulk insert (memasukkan banyak data sekaligus).
                // Ini jauh lebih efisien daripada membuat satu per satu di dalam loop.
                Category::insert($categories);

                // Mengembalikan respons JSON bahwa data berhasil dibuat.
                // Status code 201 (Created) adalah standar untuk request POST yang berhasil membuat resource baru.
                return Response::json([
                    'message' => 'Spot created successfully',
                    'data' => $spot
                ], 201);
            }

            // Jika karena suatu alasan spot gagal dibuat, kembalikan error.
            return Response::json([
                'message' => 'Spot not created',
                'data' => null
            ], 500);
        } catch (Exception $e) {
            // Menangkap error jika terjadi.
            return Response::json([
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    // ...

    /**
     * Display the specified resource.
     */
    public function show(Spot $spot)
    {
        try {
            // Mengembalikan data spot yang ditemukan.
            return Response::json([
                'message' => 'Spot details',
                // Kita menggunakan 'load' untuk Lazy Eager Loading. Ini berguna ketika kita sudah
                // memiliki instance model ($spot) dan ingin memuat relasinya setelahnya.
                'data' => $spot->load([
                    'categories:category,spot_id', // Memuat relasi categories.
                    'user:id,name', // Memuat relasi user.
                    'reviews' => function ($query) {
                        // Memuat relasi reviews, dan untuk setiap review,
                        // kita juga memuat relasi 'user'-nya (siapa yang menulis review).
                        $query->with('user:id,name');
                    }
                ])
                    // Sama seperti di index, kita juga memuat jumlah dan total rating review
                    // untuk spot spesifik ini.
                    ->loadCount(['reviews'])
                    ->loadSum('reviews', 'rating')
            ]);
        } catch (Exception $e) {
            // Menangkap error jika terjadi.
            return Response::json([
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpotRequest $request, Spot $spot)
    {
        try {
            // Mengambil data yang sudah lolos validasi dari UpdateSpotRequest.
            $validated = $request->safe()->all();

            // Cek apakah ada file 'picture' baru yang di-upload.
            if (isset($validated['picture'])) {
                // Jika ada, upload file baru dan simpan path-nya.
                $picture_path = Storage::disk('public')->putFile('spots', $request->file('picture'));
            }

            // Cek apakah ada data 'category' baru yang dikirim.
            if (isset($validated['category'])) {
                // Hapus semua kategori lama yang terkait dengan spot ini.
                Category::where('spot_id', $spot->id)->delete();

                $categories = [];
                // Buat ulang data kategori dengan data yang baru.
                foreach ($validated['category'] as $category) {
                    $categories[] = [
                        'spot_id' => $spot->id,
                        'category' => $category,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                // Masukkan data kategori yang baru ke database.
                Category::insert($categories);
            }

            // Memperbarui data pada model Spot.
            $spot->update([
                'name' => $validated['name'],
                'address' => $validated['address'],
                // Gunakan path gambar baru jika ada ($picture_path),
                // jika tidak, gunakan path gambar yang lama ($spot->picture).
                // Ini disebut Null Coalescing Operator.
                'picture' => $picture_path ?? $spot->picture
            ]);

            // Mengembalikan respons sukses dengan data spot yang sudah diperbarui.
            return Response::json([
                'message' => 'Spot updated successfully',
                'data' => $spot
            ], 200);
        } catch (Exception $e) {
            // Menangkap error.
            return Response::json([
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Spot $spot)
    {
        try {
            // Mengambil data user yang sedang login.
            $user = Auth::user();

            // Ini adalah lapisan otorisasi sederhana.
            // Pengecekan: apakah user yang ingin menghapus adalah pemilik spot tersebut ATAU seorang ADMIN?
            if ($spot->user_id != $user->id && $user->role != "ADMIN") {
                // Jika bukan keduanya, kembalikan pesan error.
                return Response::json([
                    'message' => 'You are not authorized to delete this spot',
                    'data' => null
                ], 403);
            }

            // Jika otorisasi berhasil, panggil method delete().
            // Karena kita menggunakan SoftDeletes pada model Spot, ini tidak akan menghapus data
            // secara permanen, melainkan hanya mengisi kolom 'deleted_at'.
            if ($spot->delete()) {
                // Jika berhasil, kembalikan pesan sukses.
                return Response::json([
                    'message' => 'Spot deleted successfully',
                    'data' => null
                ], 200);
            }

            // Jika karena suatu alasan gagal dihapus.
            return Response::json([
                'message' => 'Spot could not be deleted',
                'data' => null
            ], 500);
        } catch (Exception $e) {
            // Menangkap error.
            return Response::json([
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

}
