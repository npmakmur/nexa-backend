<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LokasiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            "gedung" => "required",
            "titik_lokasi" => "required",
        ]);
        $id = DB::table('tabel_master_lokasi')->insertGetId([
             'kode_lokasi' => 'TEMP',
            'lokasi_level_1' => $request->gedung,
            'lokasi_level_2' => $request->titik_lokasi,
            'kode_customer' => auth()->user()->kode_customer,
        ]);

       $kode_lokasi = 'LOKASI.' . $id;

        DB::table('tabel_master_lokasi')
            ->where('id', $id)
            ->update(['kode_lokasi' => $kode_lokasi]);
        return response()->json([
            'message' => 'Berhasil menambah Lokasi',
        ], 201);
    }
    public function update(Request $request)
    {
        // Periksa apakah data dengan ID tersebut ada
        $lokasi = DB::table('tabel_master_lokasi')->where('id', $request->id)->first();
        if (!$lokasi) {
            return response()->json([
                'message' => 'Lokasi tidak ditemukan.',
            ], 404);
        }
        // Lakukan update
        DB::table('tabel_master_lokasi')
            ->where('id', $request->id)
            ->update([
                'lokasi_level_1' => $request->gedung,
                'lokasi_level_2' => $request->titik_lokasi,
            ]);

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui.',
        ],200);
    }
    public function listLokasi (Request $request)
    {
        $data = DB::table("tabel_master_lokasi")->where("kode_customer", auth()->user()->kode_customer)->get();
         return response()->json([
            'message' => 'List Lokasi.',
            'data' => $data
        ],200);
    }
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $lokasi = DB::table('tabel_master_lokasi')->where('id', $request->id)->first();

        if (!$lokasi) {
            return response()->json([
                'message' => 'Lokasi tidak ditemukan.',
            ], 404);
        }

        DB::table('tabel_master_lokasi')->where('id', $request->id)->delete();

        return response()->json([
            'message' => 'Lokasi berhasil dihapus.',
        ]);
    }

}
