<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class AuthenticationController extends Controller
{
    public function login(LoginRequest $request)
    {
        // Memulai blok try-catch untuk menangani potensi error selama proses login.
				try {
				    // 1. Mengambil kredensial (misalnya, email dan password) dari request yang sudah lolos validasi.
				    $validated = $request->safe()->all();

				    // 2. Mencoba melakukan autentikasi menggunakan kredensial yang diberikan.
				    // `Auth::attempt()` akan otomatis melakukan hash pada password inputan dan membandingkannya dengan hash di database.
				    if (!Auth::attempt($validated)) {
				        // Jika autentikasi gagal (email atau password salah), kirim response error.
				        return Response::json([
				            'message' => 'Invalid credentials', // Pesan bahwa kredensial tidak valid.
				            'data' => null
				        ], 401); // Status HTTP 401 menandakan "Unauthorized" (tidak terotorisasi).
				    }

				    // 3. Jika autentikasi berhasil, ambil data lengkap dari user yang sedang login.
				    $user = $request->user();

				    // 4. Membuat token API baru untuk user tersebut menggunakan Laravel Sanctum.
				    // 'auth_token' adalah nama token, dan `plainTextToken` adalah token yang akan dikirim ke client.
				    $token = $user->createToken('auth_token')->plainTextToken;

				    // 5. Kirim response JSON yang menandakan login berhasil.
				    return Response::json([
				        'message' => 'Login successful',
				        // Sertakan beberapa data user yang relevan.
				        'user' => [
				            'id' => $user->id,
				            'name' => $user->name,
				            'email' => $user->email,
				        ],
				        // Sertakan token agar client bisa menggunakannya untuk request selanjutnya.
				        'access_token' => $token,
				    ], 200); // Status HTTP 200 menandakan "OK" (berhasil).

				} catch (Exception $e) {
				    // Jika terjadi error tak terduga (misalnya, koneksi database gagal) di dalam blok `try`.
				    return Response::json([
				        'message' => $e->getMessage(), // Kirim pesan error spesifik dari sistem.
				        'data' => null
				    ], 500); // Status HTTP 500 menandakan "Internal Server Error".
				}
    }

    public function register(RegisterRequest $request)
    {
        // Memulai blok try-catch untuk menangani potensi error selama proses registrasi.
				try {
				    // 1. Mengambil semua data dari request yang sudah lolos validasi.
				    // Metode `safe()` memastikan hanya data yang terdefinisi dalam aturan validasi yang diambil.
				    $validated = $request->safe()->all();

				    // 2. Melakukan hashing (enkripsi) pada password yang diterima dari inputan user untuk keamanan.
				    $passwordHash = Hash::make($validated['password']);

				    // 3. Mengganti nilai password di dalam array `$validated` dengan password yang sudah di-hash.
				    $validated['password'] = $passwordHash;

				    // 4. Membuat record user baru di dalam database menggunakan data dari array `$validated`.
				    $response = User::create($validated);

				    // 5. Memeriksa apakah proses pembuatan user berhasil.
				    if ($response) {
				        // Jika berhasil, kirimkan response JSON dengan pesan sukses dan data user yang baru dibuat.
				        // Status HTTP 201 menandakan "Created" (sumber daya baru berhasil dibuat).
				        return Response::json([
				            'message' => 'User registered successfully',
				            'data' => null
				        ], 201);
				    }
				} catch (Exception $e) {
				    // Jika terjadi error di dalam blok `try`, blok `catch` akan dieksekusi.
				    // Variabel `$e` berisi detail dari error yang terjadi.
				    return Response::json([
				        'message' => $e->getMessage(), // Pesan error spesifik dari sistem.
				        'data' => null
				    ], 500); // Status HTTP 500 menandakan "Internal Server Error".
				}
    }

    public function logout(Request $request)
    {
        try {
            // Ambil user yang sedang login
            // ambil tokennya terus hapus
            $request->user()->currentAccessToken()->delete();

            // berikan response jika berhasil logout
            return response()->json([
                'message' => 'Berhasil Logout',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
