<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TabelHeaderJadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
 public function store(Request $request)
 {
     $validator = Validator::make($request->all(), [
        'inspeksi_title' => 'required|string',
        'kode_barang'   => 'required|exists:tabel_produk,kode_barang',
        'inspeksi_pic'   => 'required|exists:users,id',
    ]);

    $cek = Product::where('kode_barang', $request->kode_barang)
        ->where('kode_customer', auth()->user()->kode_customer)
        ->first();
    if (!$cek) {
        return response()->json([
            'message' => 'Apar tidak ditemukan.'
        ], 404);
    }
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    $noJadwal = "A1/". auth()->user()->kode_customer . "/". Carbon::now()->format('YmdHis');
    $jadwal = new TabelHeaderJadwal();
    $jadwal->no_jadwal = $noJadwal;
    $jadwal->kode_barang = $request->kode_barang;
    $jadwal->inspeksi_title = $request->inspeksi_title;
    $jadwal->inspeksi_pic = $request->inspeksi_pic;
    $jadwal->status = '0';
    $jadwal->kode_customer = auth()->user()->kode_customer;
    $jadwal->kode_activity = "A1";
    $jadwal->keterangan = $request->keterangan;
    $jadwal->created_by = auth()->user()->id ?? 'system';
    $jadwal->execute_by = $request->inspeksi_pic;
    $jadwal->save();

    return response()->json([
        'message' => 'Jadwal berhasil ditambahkan.',
        'data' => $jadwal
    ], 201);
 }
 public function question (Request $request)
 {
     $getQuestion = DB::table("tabel_detail_activity")->where('kode_activity','A1')->get()->map(function ($item) {
        $kondisi = DB::table("tabel_detail_kondisi")->where("kode_detail_activity", $item->kode_detail_activity)->get();
        $item->detail_kondisi = $kondisi;
        return $item;
    });
      return response()->json([
        'message' => 'list inspeksi dan detail kondisi.',
        'data' => $getQuestion
    ], 201);
 }

}
