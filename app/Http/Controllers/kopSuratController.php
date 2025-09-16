<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class kopSuratController extends Controller
{
    public function insertKopSurat(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
            "type" => "required",
        ]);

        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = 'kop_surat_' . $request->type . '_' . time() . '.' . $extension;

        // Simpan file baru
        $path = $file->storeAs('kop_surat', $filename, 'public');
        $cleanPath = str_replace('public/', '', $path);

        // Ambil data lama jika ada
        $oldKopSurat = DB::table("kop_surat")->where("type", $request->type)->first();
        
        // Hapus file lama jika ada
        if ($oldKopSurat) {
            $oldImagePath = str_replace('/storage', 'public', $oldKopSurat->image);
            Storage::delete($oldImagePath);
        }

        // Update atau insert data baru
        $updateOrInsert = DB::table("kop_surat")->updateOrInsert(
            [
                "type" => $request->type,
                "kode_customer" => auth()->user()->kode_customer
            ], // Atribut yang digunakan untuk mencari (kondisi WHERE)
            [
                "kode_customer" => auth()->user()->kode_customer,
                "image" => $cleanPath,
                "updated_at" => now()
            ]
        );

        return response()->json([
            'message' => 'Kop surat berhasil diperbarui/disimpan',
        ], 200);
    }
    public function updateKopSurat(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
            "type" => "required",
            "aktif" => "required",
        ]);
        
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = 'kop_surat_' . $request->type . '_' . time() . '.' . $extension;

        // Simpan di folder 'kop_surat'
        $path = $file->storeAs('kop_surat', $filename, 'public');
        $cleanPath = str_replace('public/', '', $path);
        $url = Storage::url($path);

        // Cek apakah type sudah ada
        $exists = DB::table("kop_surat")->where("type", $request->type)->first();

        if ($exists) {
            // Update
            DB::table("kop_surat")
                ->where("type", $request->type)
                ->where("kode_customer", auth()->user()->kode_customer)
                ->update([
                    "image" => $cleanPath,
                    "aktif" => $request->aktif,
                    "updated_at" => now()
                ]);
        } else {
            // Insert baru
            DB::table("kop_surat")->insert([
                "type" => $request->type,
                "aktif" => $request->aktif,
                "image" => $cleanPath,
                "created_at" => now(),
                "kode_customer" => auth()->user()->kode_customer,

                "updated_at" => now()
            ]);
        }

        return response()->json([
            'message' => 'Kop surat berhasil disimpan',
        ], 200);
    }
    public function listKopSurat(Request $request)
    {
        // Mulai query ke tabel 'kop_surat'
        $query = DB::table('kop_surat');

        // Cek apakah request memiliki parameter 'type'
        if ($request->has('type') && $request->type != '') {
            // Jika ada, tambahkan kondisi WHERE untuk memfilter berdasarkan type
            $query->where('type', $request->type);
        }
        
        // Ambil data dari database
        $kopSuratList = $query->get();

        // Ubah path gambar menjadi URL yang bisa diakses publik
        $kopSuratList = $kopSuratList->map(function ($item) {
            $item->image_url = Storage::url($item->image);
            return $item;
        });

        // Mengembalikan data dalam format JSON
        return response()->json([
            'message' => 'Daftar kop surat berhasil diambil',
            'data' => $kopSuratList
        ], 200);
    }
}
