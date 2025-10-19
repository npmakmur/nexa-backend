<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'level' => 'required|exists:tabel_level,id',
            'gender' => 'required',
        ], [
            'name.required' => 'Nama tidak boleh kosong.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',

            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan, pilih yang lain.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',

            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',

            'level.required' => 'Level wajib dipilih.',
            'level.exists' => 'Level tidak ditemukan dalam database.',

            'gender.required' => 'Jenis kelamin wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }


        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->kode_customer = auth()->user()->kode_customer;
        $user->id_level = $request->level;
        $user->akun_aktif = 1;
        $user->gender = $request->gender;
        $user->created_by = auth()->user()->id;
        $user->save();
        $aktivitas = Aktivitas::create([
            'aktivitas_by' => auth()->user()->id,
            'aktivitas_name' => 'Menambahkan pengguna baru dengan username: '.$request->username ,
            'tanggal' => now(),
            'created_by' => auth()->user()->id,
            'created_at' => now(),
        ]);
        return response()->json([
            'message' => 'User berhasil ditambahkan',
            'data' => $user,
        ],201);
    }
    public function update(Request $request)
    {
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                Rule::unique('users')->ignore($user->id)
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'level' => 'required|exists:tabel_level,id',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'string|min:8',
        ], [
            'password.min'      => 'Password minimal 8 karakter',
            'email.required'    => 'Email harus diisi',
            'email.email'       => 'Format email tidak valid',
            'name.required'     => 'Nama harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(), // ambil pesan pertama
                'errors' => $validator->errors()
            ], 422);
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->id_level = $request->level;
        $user->updated_by = auth()->user()->id;
        if ($request->hasFile('foto_profile')) {
        // Hapus foto lama jika ada
            if ($user->image && Storage::exists($user->image)) {
                Storage::delete($user->image);
            }

            $file = $request->file('foto_profile');
            $extension = $file->getClientOriginalExtension();
            // Buat nama unik: userID_timestamp.ext
            $filename = 'foto_' . $user->id . '_' . time() . '.' . $extension;

            // Simpan di folder 'public/foto_profil'
            $path =  $file->storeAs('foto_profil', $filename, 'public');

            // Simpan path ke database
            $user->image = asset('storage/' . $path);
        }
        $user->save();
        $aktivitas = Aktivitas::create([
            'aktivitas_by' => auth()->user()->id,
            'aktivitas_name' => 'Memperbarui data pengguna dengan id : '. $request->id,
            'tanggal' => now(),
            'created_by' => auth()->user()->id,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'data' => $user,
        ], 200);
    }
    public function updateFotoProfile(Request $request)
    {
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $request->validate([
            'foto_profile' => 'required|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
        ]);

        if ($request->hasFile('foto_profile')) {
            // Hapus foto lama jika ada
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $file = $request->file('foto_profile');
            $extension = $file->getClientOriginalExtension();
            // Buat nama unik: userID_timestamp.ext
            $filename = 'foto_' . $user->id . '_' . time() . '.' . $extension;

            // Simpan di folder 'public/foto_profil'
            $path = $file->storeAs('foto_profil', $filename, 'public');

            // Simpan path ke database
            $user->image = asset('storage/' . $path);
            $user->updated_by = auth()->user()->id;
            $user->save();
        }

        // Simpan log aktivitas
        Aktivitas::create([
            'aktivitas_by' => auth()->user()->id,
            'aktivitas_name' => 'Memperbarui foto profil user dengan id : ' . $user->id,
            'tanggal' => now(),
            'created_by' => auth()->user()->id,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Foto profil berhasil diperbarui',
            'data' => $user,
        ], 200);
    }

    public function listUser (Request $request)
    {
      $query = User::where("users.kode_customer", auth()->user()->kode_customer)
        ->where("users.akun_aktif", 1)
        ->leftJoin('tabel_level', 'users.id_level', '=', 'tabel_level.id')
        ->select(
            "users.*",
            "tabel_level.nama_level"
        )
        ->orderByRaw("LEFT(name, 1) ASC");
        if ($request->filled('id_level')) {
            $query->where('id_level', $request->id_level);
        }
        $user = $query->get();
        return response()->json([
            'message' => 'List User',
            'data' => $user,
        ], 200);
    }
    public function detailUser (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }
        $user = DB::table('users')
        ->leftJoin('tabel_master_customer', 'users.kode_customer', '=', 'tabel_master_customer.kode_customer')
         ->leftJoin('tabel_level', 'users.id_level', '=', 'tabel_level.id')
        ->where('users.id', $request->id)
        ->where('users.akun_aktif', 1)
        ->select(
            'users.*',
            'tabel_master_customer.nama_customer',
             "tabel_level.nama_level"
        )
        ->first();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User tidak ditemukan atau akun tidak aktif',
            ], 404);
        }
        return response()->json([
            'message' => 'Detail User',
            'data' => $user,
        ], 200);
    }
    public function destroy(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->delete();
        $aktivitas = Aktivitas::create([
            'aktivitas_by' => auth()->user()->id,
            'aktivitas_name' => 'Menghapus data pengguna dengan id : '. $request->id,
            'tanggal' => now(),
            'created_by' => auth()->user()->id,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'User berhasil dihapus'
        ], 200);
    }
    public function countUser(Request $request)
    {
        $kodeCustomer = auth()->user()->kode_customer;
        $userLevels = DB::table('tabel_level')
            ->leftJoin('users', function($join) use ($kodeCustomer) {
                $join->on('tabel_level.id', '=', 'users.id_level')
                     ->where('users.akun_aktif', 1)
                     ->whereNull('users.deleted_at')
                     ->where('users.kode_customer', $kodeCustomer);
            })
            ->select(
                'tabel_level.id',
                'tabel_level.nama_level',
                DB::raw('COUNT(users.id) as jumlah_user')
            )
            ->groupBy('tabel_level.id', 'tabel_level.nama_level')
            ->get();


        return response()->json([
            'message' => 'Jumlah user aktif per level',
            'data' => $userLevels,
        ], 200);
    }
    public function listLevelUser (Request $request)
    {
        $level = DB::table("tabel_level")->get();
         return response()->json([
            'message' => 'Data level',
            'data' => $level,
        ], 200);
    }
    public function listKodeCustomer (Request $request)
    {
        $tabel_master_customer = DB::table("tabel_master_customer")->get();
         return response()->json([
            'message' => 'Data list kode customer',
            'data' => $tabel_master_customer,
        ], 200);
    }
    public function listAllCustomer (Request $request)
    {
        $query = User::leftJoin('tabel_level', 'users.id_level', '=', 'tabel_level.id')
        ->select(
            "users.*",
            "tabel_level.nama_level"
        )
        ->orderByRaw("LEFT(name, 1) ASC");
        if ($request->filled('email')) {
            $query->where('email', $request->email);
        }
        $user = $query->get();
        return response()->json([
            'message' => 'List User',
            'data' => $user,
        ], 200);
    }
    public function updatePassCustomer (Request $request)
    {
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8',
        ], [
            'password.min'      => 'Password minimal 8 karakter',
            'password.required'    => 'Password harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(), // ambil pesan pertama
                'errors' => $validator->errors()
            ], 422);
        }
        $user->password = Hash::make($request->password);
        $user->updated_by = auth()->user()->id;
        $user->save();
        $aktivitas = Aktivitas::create([
            'aktivitas_by' => auth()->user()->id,
            'aktivitas_name' => 'Memperbarui data pengguna dengan id : '. $request->id,
            'tanggal' => now(),
            'created_by' => auth()->user()->id,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Password User berhasil diperbarui',
        ], 200);
    }
}
