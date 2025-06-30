<?php

namespace App\Http\Controllers;

use App\Mail\VerifikasiMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'jenis_customer' => 'required',
            'alamat' => 'required',
            'telpon' => 'required',
            'nama_organisasi' => 'required',
            'gender' => 'required'
        ]);
        $kode_customer = $this->generateUniqueCustomerCode();
        $code_verifikasi = random_int(100000, 999999);
        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->code_verifikasi = $code_verifikasi;
        $user->kode_customer = $kode_customer;
        $user->email_verified_at = now();
        $user->gender = $request->gender;
        $user->id_level = 1;
        $user->save();
  
        $customer = DB::table('tabel_master_customer')->insert([
            'kode_customer' => $kode_customer,
            'nama_customer' => $request->nama_organisasi,
            'jenis_customer' => $request->jenis_customer,
            'alamat' => $request->alamat,
            'telp' => $request->telpon,
            'email' => $request->email_organisasi,
            'created_at' => now(),
        ]);

        if ($user->save() && $customer ) {
            $subject = "Verfikasi Email";
            $teks = "Terima kasih telah mendaftar! Untuk menyelesaikan proses pendaftaran, silakan masukkan kode verifikasi berikut:";
            Mail::to($user->email)->send(new VerifikasiMail($user->nama, $code_verifikasi,$teks, $subject));

        } else {
            return response()->json(['message' => 'Registrasi gagal'], 500);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        $user->remember_token = $token;
        $user->save();
        return response()->json([
            'message' => 'User berhasil terdaftar',
            'token' => $token, // Token yang digunakan untuk autentikasi
            'user' => $user
        ], 201);
    }
    public function generateUniqueCustomerCode()
    {
        do {
            $randomNumber = rand(1000, 9999);
            $code = 'CSTM-' . $randomNumber;

            $exists = DB::table('tabel_master_customer')->where('kode_customer', $code)->exists();
        } while ($exists);

        return $code;
    }
    public function emailVerifed(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'verifed_code' => 'required',
        ]);

        $cek = User::where('id', $request->id)
        ->where('akun_aktif',0)
        ->where('code_verifikasi', $request->verifed_code)
        ->first();
        if (!$cek) {
            return response()->json(['message' => 'Kode verifikasi salah.'], 404);
        }
        
        // Cek apakah sudah lebih dari 5 menit
        $kodeDibuat = Carbon::parse($cek->email_verified_at);
        $sekarang = Carbon::now();
        if ($kodeDibuat->diffInMinutes($sekarang) > 5) {
            return response()->json(['message' => 'Kode verifikasi sudah kadaluarsa.'], 410);
        }
        $cek->email_verified_at = now(); 
        $cek->code_verifikasi = null; 
        $cek->akun_aktif = 1; 
        $cek->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Email berhasil diverifikasi.',
            'user' => $cek,
        ], 200);
        

        


    }
    public function generateCodeVerify(Request $request)
    {
        $user = User::where('id', $request->id)
        ->where('akun_aktif', 0)
        ->first();
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan atau sudah aktif.'
            ], 404);
        }
        $nama = $user->name;
        $code_verifikasi = random_int(100000, 999999);
        $user->code_verifikasi = $code_verifikasi;
        $user->email_verified_at = now();
        $user->save();
        $subject = "Kode Barumu Sudah Siap!";
        $teks = "Halo! Kode barumu sudah berhasil dibuat. Silakan gunakan kode berikut untuk melanjutkan prosesmu. Jangan kasih tahu siapa-siapa ya! 😊";
        if ($user->save()) {
            Mail::to($user->email)->send(new VerifikasiMail($nama, $code_verifikasi,$teks, $subject));
            return response()->json([
                'message' => 'Generate Kode Berhasil',
            ], 201);
        } else {
            return response()->json(['message' => 'Gagal Membuat Code'], 500);
        }
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }
        $user = auth()->user();
        $user->remember_token = $token;
        $user->save();
    
        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }
    public function forgetPassword (Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $user = User::where('email', $request->email)
        ->where('akun_aktif', 1)
        ->first();
        $nama = $user->name;
        $code_verifikasi = random_int(100000, 999999);
        $user->code_verifikasi = $code_verifikasi;
        $user->updated_at = now();
        $user->save();
        if ($user->save()) {
            $subject = "Lupa Password";
            $teks = "Kami menerima permintaan untuk mereset kata sandi akun Anda. Berikut adalah kode verifikasi untuk melanjutkan proses reset kata sandi Anda:";
            Mail::to($user->email)->send(new VerifikasiMail($nama, $code_verifikasi,$teks, $subject));
            // Mail::to($user->email)->queue(new VerifikasiMail($nama, $code_verifikasi,$teks, $subject));
            return response()->json([
                'data' => $user,
                'message' => 'Generate Kode Berhasil',
            ], 201);
        } else {
            return response()->json(['message' => 'Gagal Membuat Code'], 500);
        }
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'verifed_code' => 'required',
            'new_password' => 'required'
        ]);

        $cek = User::where('id', $request->id)
        ->where('akun_aktif',1)
        ->where('code_verifikasi', $request->verifed_code)
        ->first();
        if (!$cek) {
            return response()->json(['message' => 'Kode verifikasi salah.'], 404);
        }
        
        // Cek apakah sudah lebih dari 5 menit
        $kodeDibuat = Carbon::parse($cek->updated_at);
        $sekarang = Carbon::now();
        if ($kodeDibuat->diffInMinutes($sekarang) > 5) {
            return response()->json(['message' => 'Kode sudah kadaluarsa.'], 410);
        }
        $newPassword = Hash::make($request->new_password);
        $cek->code_verifikasi = null; 
        $cek->password = $newPassword; 
        $cek->updated_at = now(); 
        $cek->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah.',
            'user' => $cek,
        ], 200);
    }
    public function logout(Request $request)
    {
        try {
            // Invalidate token yang sedang aktif
            JWTAuth::invalidate(JWTAuth::parseToken());
    
            return response()->json([
                'message' => 'Logout berhasil, token telah dihapus'
            ],200);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Gagal logout, token tidak ditemukan atau sudah tidak valid'
            ], 500);
        }
    }
    public function gender(Request $request)
    {
        $gander = DB::table("tabel_master_gender")->get();
       return response()->json([
                'message' => 'List Gander',
                'data_gander' => $gander
        ], 200);
    }
}
