<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class kopSuratController extends Controller
{
public function insertKopSurat (Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
            "type" => "required"
        ]);
        
        // Ambil file menggunakan nama 'image' yang sudah divalidasi
        $file = $request->file('image');
        
        $extension = $file->getClientOriginalExtension();
        // Buat nama unik: kop_surat_type_timestamp.ext
        $filename = 'kop_surat_' . $request->type . '_' . time() . '.' . $extension;

        // Simpan di folder 'kop_surat'
        $path = $file->storeAs('kop_surat', $filename, 'public');
        $url = Storage::url($path);

        $insert = DB::table("kop_surat")->insert([
            "type" => $request->type, // Ubah 'type' menjadi 'name' sesuai tabel Anda
            "image" => $url, // Ubah 'image' menjadi 'logo_path'
        ]);


        return response()->json([
            'message' => 'Kop surat berhasil disimpan',
        ], 200);
    }
}
