<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LokasiController extends Controller
{
    public function storeGedung(Request $request)
    {
        $request->validate([
            'gedung' => 'required|string|max:191',
        ]);
        do {
            $kode_lokasi = 'LOKASI.' . Str::upper(Str::random(6));
        } while (
            DB::table('tabel_gedung')->where('kode_lokasi', $kode_lokasi)->exists()
        );
        $id = DB::table('tabel_gedung')->insertGetId([
            'nama_gedung'   => $request->gedung,
            'kode_lokasi'   => $kode_lokasi,
            'kode_customer' => auth()->user()->kode_customer,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'message' => 'Gedung berhasil disimpan.',
        ],200);
    }
    public function locationPoint (Request $request)
    {
        $request->validate([
            "gedung_id" => "required",
            "nama_titik" => "required"
        ]);
        $create = DB::table("tabel_titik_penempatan")->insert([
            "nama_titik" => $request->nama_titik,
            "gedung_id" => $request->gedung_id
        ]);
        return response()->json([
            'message' => "titik penempatan berhasil disimpan"
        ], 200);
    }
    public function listLokasi (Request $request)
    {
        $data_location = DB::table("tabel_gedung")
        ->where("kode_customer",auth()->user()->kode_customer)
        ->orderBy("id", "desc")
        ->get()
        ->map(function($data){
            $data->location_point = DB::table('tabel_titik_penempatan')->where("gedung_id", $data->id)->get();
            return $data;
        });
        return response()->json([
            "message" =>"data location berhasil diambil",
            "data" => $data_location
        ], 200);
    }
    public function updateGedung(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'gedung' => 'required|string|max:191',
        ]);
        $id = $request->id;

        DB::table('tabel_gedung')->where('id', $id)->update([
            'nama_gedung' => $request->gedung,
            'updated_at'  => now(),
        ]);

        return response()->json([
            'message' => 'Gedung berhasil diperbarui.',
        ], 200);
    }
    public function destroyGedung(Request $request)
    {
        $id = $request->id;
        DB::table('tabel_gedung')->where('id', $id)->delete();

        return response()->json([
            'message' => 'Gedung berhasil dihapus.',
        ], 200);
    }
    public function updateTitik(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'nama_titik' => 'required|string|max:191',
        ]);
        $id = $request->id;

        DB::table('tabel_titik_penempatan')->where('id', $id)->update([
            'nama_titik' => $request->nama_titik,
        ]);

        return response()->json([
            'message' => 'Titik lokasi berhasil diperbarui.',
        ], 200);
    }
    public function destroyTitik(Request $request)
    {
        $id = $request->id;
        DB::table('tabel_titik_penempatan')->where('id', $id)->delete();

        return response()->json([
            'message' => 'Titik lokasi berhasil dihapus.',
        ], 200);
    }
    public function apiListLocationPoint(Request $request)
    {
        $request->validate([
            'gedung_id' => 'required'
        ]);
        $titik = DB::table('tabel_titik_penempatan')
                    ->where('gedung_id', $request->gedung_id)
                    ->orderBy('id', 'desc')
                    ->get();

        return response()->json([
            'message' => 'List titik lokasi berhasil diambil.',
            'data' => $titik
        ], 200);
    }
    public function apiListGedung(Request $request)
    {
        $kode_customer = $request->kode_customer ?? auth()->user()->kode_customer;

        $gedung = DB::table('tabel_gedung')
                    ->where('kode_customer', $kode_customer)
                    ->select('id', 'nama_gedung', 'kode_lokasi')
                    ->orderBy('id', 'desc')
                    ->get();

        return response()->json([
            'message' => 'List gedung berhasil diambil.',
            'data' => $gedung
        ], 200);
    }






}
