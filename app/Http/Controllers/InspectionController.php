<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\Product;
use App\Models\TabelHeaderJadwal;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InspectionController extends Controller
{
 public function store(Request $request)
 {
     $validator = Validator::make($request->all(), [
        'inspeksi_title' => 'required|string',
        'inspeksi_pic'   => 'required|exists:users,id',
        'tanggal_mulai'     => 'required|date',
        'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_produksi',
    ]);
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $noJadwal = "A1/". auth()->user()->kode_customer . "/". Carbon::now()->format('YmdHis');
    $jadwal = new TabelHeaderJadwal();
    $jadwal->no_jadwal = $noJadwal;
    $jadwal->inspeksi_title = $request->inspeksi_title;
    $jadwal->inspeksi_pic = $request->inspeksi_pic;
    $jadwal->status = 'Belum dikerjakan';
    $jadwal->kode_customer = auth()->user()->kode_customer;
    $jadwal->kode_activity = "A1";
    $jadwal->keterangan = $request->keterangan;
    $jadwal->created_by = auth()->user()->id ?? 'system';
    $jadwal->execute_by = $request->inspeksi_pic;
    $jadwal->tgl_mulai = $request->tanggal_mulai;
    $jadwal->tgl_selesai = $request->tanggal_selesai;

    $jadwal->save();

    return response()->json([
        'message' => 'Jadwal berhasil ditambahkan.',
    ], 201);
 }
 public function destroy (Request $request)
 {
     $jadwal = TabelHeaderJadwal::where("id",$request->id_jadwal)->first();

    if (!$jadwal) {
        return response()->json([
            'message' => 'Data jadwal tidak ditemukan.'
        ], 404);
    }

    $jadwal->delete();
    $inspection = DB::table("tabel_inspection")->where("no_jadwal", $jadwal->no_jadwal)->delete();

    return response()->json([
        'message' => 'Jadwal berhasil dihapus.'
    ], 200);
 }
 public function changeStatusInspection (Request $request)
 {
   $request->validate([
        'id_jadwal' => 'required|exists:tabel_header_jadwal,id',
        'status'    => 'required|string'
    ]);

    $jadwal = TabelHeaderJadwal::find($request->id_jadwal);

    if (!$jadwal) {
        return response()->json([
            'message' => 'Data jadwal tidak ditemukan.'
        ], 404);
    }

    if ($jadwal->status !== 'On progress') {
        return response()->json([
            'message' => 'Status hanya bisa diubah jika dalam kondisi "On progress".'
        ], 403); // 403 Forbidden lebih sesuai dibanding 401
    }

    $jadwal->status = $request->status;
    $jadwal->save();

    return response()->json([
        'message' => 'Status jadwal berhasil diperbarui.',
        'data'    => $jadwal
    ], 200);

 }
 public function update(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id_jadwal'         => 'required',
        'inspeksi_title'    => 'required|string',
        'inspeksi_pic'      => 'required|exists:users,id',
        'tanggal_mulai'     => 'required|date',
        'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai', // diperbaiki dari 'tanggal_produksi'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    $jadwal = TabelHeaderJadwal::find($request->id_jadwal);

    if (!$jadwal) {
        return response()->json(['message' => 'Data tidak ditemukan.'], 404);
    }

    // Update data
    $jadwal->inspeksi_title = $request->inspeksi_title;
    $jadwal->inspeksi_pic = $request->inspeksi_pic;
    $jadwal->kode_customer = auth()->user()->kode_customer;
    $jadwal->kode_activity = "A1";
    $jadwal->keterangan = $request->keterangan;
    $jadwal->execute_by = $request->inspeksi_pic;
    $jadwal->tgl_mulai = $request->tanggal_mulai;
    $jadwal->tgl_selesai = $request->tanggal_selesai;

    $jadwal->save();

    return response()->json([
        'message' => 'Jadwal berhasil diperbarui.',
        'data' => $jadwal
    ], 200);
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
        'data_question' => $getQuestion
    ], 201);
 }
 function getStatusAparById(array $jawaban): string
 {
    $bermasalah = [
        2, 3, 5, 6, 8, 9, 10, 12, 13, 14, 16, 17, 19, 20, 21, 24
    ];

    $masalah = [];

    foreach ($jawaban as $komponen => $idKondisi) {
        if (in_array($idKondisi, $bermasalah)) {
            $masalah[] = $komponen;
        }
    }

    if (empty($masalah)) {
        return 'Ok';
    }

    if (count($masalah) === 1 && in_array($jawaban['need_refill'], [24])) {
        return 'butuh isi ulang';
    }

    if (count($masalah) === 1 && in_array($jawaban['hidrotest'], [21])) {
        return 'belum diuji';
    }

    return 'rusak';
 }
 public function inspectionApar (Request $request)
 {
    $validator = Validator::make($request->all(), [
        'id_jadwal'          => 'required',
        'kode_barang'          => 'required',
        'pressure'          => 'required',
        'seal'              => 'required',
        'hose'              => 'required',
        'cylinder'          => 'required',
        'head_grip'         => 'required',
        'spindle_head'      => 'required',
        'hidrotest'         => 'required',
        'need_refill'       => 'required',
        'pressure_img'      => 'image',
        'seal_img'          => 'image',
        'hose_img'          => 'image',
        'cylinder_img'      => 'image',
        'head_grip_img'     => 'image',
        'spindle_head_img'  => 'image',
    ]);
    $jawaban = [
        'pressure'       => $request->pressure,
        'seal'           => $request->seal,
        'hose'           => $request->hose,
        'cylinder'       => $request->cylinder,
        'head_grip'      => $request->head_grip,
        'spindle_head'   => $request->spindle_head,
        'hidrotest'      => $request->hidrotest,
        'need_refill'    => $request->need_refill,
    ];
    $now = Carbon::now();
    // ambil data jadwal,customer, data produk
    $jadwal = TabelHeaderJadwal::where("id", $request->id_jadwal)->first();
    if ($jadwal->status === "Selesai") {
          return response()->json([
            'message' => 'Inspeksi sudah selesai.',
        ], 404);
    }elseif($jadwal->status === "Dijeda"){
          return response()->json([
            'message' => 'Ispeksi Masih Dijeda.',
        ], 404);
    }
    $nama_customer = DB::table('tabel_master_customer')->where('kode_customer',$jadwal->kode_customer)->first();
    $produk = Product::where("kode_barang",$request->kode_barang)->first();
    if (!$jadwal) {
        return response()->json(['message' => 'Jadwal tidak ditemukan.'], 404);
    }
    // update inspection
    $paths = [];

    $fields = [
        'pressure_img',
        'seal_img',
        'hose_img',
        'cylinder_img',
        'head_grip_img',
        'spindle_head_img',
    ];
    $status = $this->getStatusAparById($jawaban);

    foreach ($fields as $field) {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $timestamp = Carbon::now()->format('Ymd_His');
            $random = Str::random(5);
            $filename = "{$field}_{$timestamp}_{$random}." . $file->getClientOriginalExtension();
            $paths[$field] = $file->storeAs('apar', $filename, 'public');
        }
    }
    $cek_inspection = DB::table("tabel_inspection")->where("id_inspection", $request->id_inspection)->first();
    if ($cek_inspection) {
       $inspection = DB::table('tabel_inspection')
            ->where('id_inspection', $cek_inspection->id_inspection)
            ->where('no_jadwal', $jadwal->no_jadwal)
            ->where('barcode', $produk->barcode)
            ->update([
                "kode_customer"      => $jadwal->kode_customer,
                "kode_barang"        => $produk->kode_barang,
                "nama_customer"      => $nama_customer->nama_customer,
                "tanggal_cek"        => Carbon::now()->format('Y-m-d'),
                "lokasi"             => $produk->lokasi ?? null,
                "kadaluarsa"         => $produk->tgl_kadaluarsa,
                "status"             => $status,
                "brand"              => $produk->brand,
                "type"               => $produk->type,
                "media"              => $produk->media,
                "kapasitas"          => $produk->kapasitas,
                "pressure"           => $request->pressure,
                "pressure_img"       => $paths['pressure_img'] ?? null,
                "seal"               => $request->seal,
                "seal_img"           => $paths['seal_img'] ?? null,
                "hose"               => $request->hose,
                "hose_img"           => $paths['hose_img'] ?? null,
                "cylinder"           => $request->cylinder,
                "cylinder_img"       => $paths['cylinder_img'] ?? null,
                "head_grip"          => $request->head_grip,
                "head_grip_img"      => $paths['head_grip_img'] ?? null,
                "spindle_head"       => $request->spindle_head,
                "spindle_head_img"   => $paths['spindle_head_img'] ?? null,
                "hidrotest"          => $request->hidrotest,
                "qc"                 => auth()->user()->id,
            ]
        );
        Aktivitas::create([
            'aktivitas_by' => auth()->id(),
            'aktivitas_name' => 'Mengupdate Inspection id inspection ' . $request->id_inspection,
            'tanggal' => $now,
            'created_by' => auth()->id(),
            'created_at' => $now,
        ]);
    }else{
        $count_inspection = DB::table('tabel_inspection')->where("no_jadwal",$jadwal->no_jadwal)->count();
        $inspection = DB::table('tabel_inspection')->insert(
            [
                "no_jadwal" => $jadwal->no_jadwal,
                "kode_customer" => $jadwal->kode_customer,
                "nama_customer" => $nama_customer->nama_customer,
                "kode_barang"        => $produk->kode_barang,
                "tanggal_cek" => Carbon::now()->format('Y-m-d'),
                "lokasi" => $produk->lokasi ?? null,
                "barcode" => $produk->barcode,
                "kadaluarsa" => $produk->tgl_kadaluarsa,
                "status" => $status,
                "brand" => $produk->brand,
                "type" => $produk->type,
                "media" => $produk->media,
                "kapasitas" => $produk->kapasitas,
                "pressure" => $request->pressure,
                "pressure_img" => $paths['pressure_img'] ?? null,
                "seal" => $request->seal,
                "seal_img" => $paths['seal_img'] ?? null,
                "hose" => $request->hose,
                "hose_img" => $paths['hose_img'] ?? null,
                "cylinder" => $request->cylinder,
                "cylinder_img" => $paths['cylinder_img'] ?? null,
                "head_grip" => $request->head_grip,
                "head_grip_img" => $paths['head_grip_img'] ?? null,
                "spindle_head" => $request->spindle_head,
                "spindle_head_img" => $paths['spindle_head_img'] ?? null,
                "hidrotest" => $request->hidrotest,
                "qc" => auth()->user()->id,
            ]
        );
        
        $jadwal->status = 'On progress';
        $jadwal->jumlah_apar = $count_inspection + 1;
        $jadwal->tgl_mulai_sebenarnya = now()->format('Y-m-d');
        $jadwal->updated_at = now(); // otomatis diisi juga kalau pakai save()
        $jadwal->save();

        $produk = Product::where("kode_barang",$request->kode_barang)
        ->update(["status" => $status]);
         Aktivitas::create([
            'aktivitas_by' => auth()->id(),
            'aktivitas_name' => 'Inspection apar ' . $request->kode_barang,
            'tanggal' => $now,
            'created_by' => auth()->id(),
            'created_at' => $now,
        ]);
    }
    $update_apar = Product::where("kode_barang",$request->kode_barang)->update([
        "pressure" => $request->pressure,
        "seal" => $request->seal,
        "hose" => $request->hose,
        "cylinder" => $request->cylinder,
        "head_grip" => $request->head_grip,
        "spindle_head" => $request->spindle_head,
        "hidrotest" => $request->hidrotest,
        "last_inspection" => $now,
    ]);
     return response()->json([
        'message' => 'Ispeksi Apar berhasil.',
    ], 201);
    

 }
 public function listInspection (Request $request)
 {
   $list = TabelHeaderJadwal::where("tabel_header_jadwal.kode_customer", auth()->user()->kode_customer)
    ->leftJoin('users', 'users.id', '=', 'tabel_header_jadwal.inspeksi_pic')
    ->select('tabel_header_jadwal.*', 'users.name as inspection_name')
    ->get();
    return response()->json([
        'message' => 'list inspeksi',
        'data_list_inspeksi' => $list,
    ], 201);
 }
 public function detailInspection (Request $request)
 {
  $validator = Validator::make($request->all(), [
        'id_jadwal'          => 'required',
    ]);
   $data = DB::table('tabel_header_jadwal')
    ->where('tabel_header_jadwal.id', $request->id_jadwal)
    ->leftJoin('users as pic', 'pic.id', '=', 'tabel_header_jadwal.inspeksi_pic')
    ->leftJoin('users as creator', 'creator.id', '=', 'tabel_header_jadwal.created_by')
    ->select(
        'tabel_header_jadwal.*',
        'pic.name as inspection_name',
        'creator.name as created_name'
    )
    ->first();
    $apar = DB::table('tabel_inspection')->where("no_jadwal", $data->no_jadwal)
    ->leftJoin('users as qc_name', 'qc_name.id', '=', 'tabel_inspection.qc')
    ->select(
        'tabel_inspection.*',
        'qc_name.name as qc_name',
    )
    ->get();
    return response()->json([
        'message' => 'detail inspeksi',
        'detail_agenda' => $data,
        'list_apar' =>$apar
    ], 201);
 }
 public function aparNotInspected (Request $request)
 {
    $validator = Validator::make($request->all(), [
        'id_jadwal'          => 'required',
    ]);
    $schedule = DB::table("tabel_header_jadwal")->where("id", $request->id_jadwal)->first();
    $inspection = DB::table("tabel_inspection")->where("no_jadwal", $schedule->no_jadwal)->pluck("kode_barang");
    $apar = Product::where("kode_customer", auth()->user()->kode_customer)->get();
    $filteredApar = $apar->reject(function ($item) use ($inspection) {
        return $inspection->contains($item->kode_barang);
    });
    return response()->json([
        'message' => 'list inspeksi',
        'data_list_apar_not_inspected' => $filteredApar,
    ], 201);
 }
 public function aparInspected (Request $request)
 {
    $validator = Validator::make($request->all(), [
        'id_jadwal'          => 'required',
    ]);
    $schedule = DB::table("tabel_header_jadwal")->where("id", $request->id_jadwal)->first();
    $inspection = DB::table("tabel_inspection")->where("no_jadwal", $schedule->no_jadwal)->pluck("kode_barang");
    $apar = Product::WhereIn("kode_barang", $inspection)->get();
    return response()->json([
        'message' => 'list inspeksi',
        'data_list_apar_inspected' => $apar,
    ], 201);
 }
 public function generateAparReport(Request $request)
 {
    $validator = Validator::make($request->all(), [
        'id_jadwal' => 'required|exists:tabel_header_jadwal,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validasi gagal',
            'errors' => $validator->errors(),
        ], 422);
    }

    $data = DB::table('tabel_header_jadwal')
        ->where('tabel_header_jadwal.id', $request->id_jadwal)
        ->leftJoin('users as pic', 'pic.id', '=', 'tabel_header_jadwal.inspeksi_pic')
        ->leftJoin('users as creator', 'creator.id', '=', 'tabel_header_jadwal.created_by')
        ->select(
            'tabel_header_jadwal.*',
            'pic.name as inspection_name',
            'creator.name as created_name'
        )
        ->first();

    $apar = DB::table('tabel_inspection')
        ->where("no_jadwal", $data->no_jadwal)
        ->leftJoin('users as qc_name', 'qc_name.id', '=', 'tabel_inspection.qc')
        ->leftJoin('tabel_detail_kondisi as pressure', 'pressure.id', '=', 'tabel_inspection.pressure')
        ->leftJoin('tabel_detail_kondisi as seal', 'seal.id', '=', 'tabel_inspection.seal')
        ->leftJoin('tabel_detail_kondisi as hose', 'hose.id', '=', 'tabel_inspection.hose')
        ->leftJoin('tabel_detail_kondisi as cylinder', 'cylinder.id', '=', 'tabel_inspection.cylinder')
        ->leftJoin('tabel_detail_kondisi as head_grip', 'head_grip.id', '=', 'tabel_inspection.head_grip')
        ->leftJoin('tabel_detail_kondisi as spindle_head', 'spindle_head.id', '=', 'tabel_inspection.spindle_head')
        ->leftJoin('tabel_detail_kondisi as hidrotest', 'hidrotest.id', '=', 'tabel_inspection.hidrotest')
        ->select(
            'tabel_inspection.*', 
            'qc_name.name as qc_name',
            'pressure.detail_kondisi as pressure_kondisi',
            'seal.detail_kondisi as seal_kondisi',
            'hose.detail_kondisi as hose_kondisi',
            'cylinder.detail_kondisi as cylinder_kondisi',
            'head_grip.detail_kondisi as head_grip_kondisi',
            'spindle_head.detail_kondisi as spindle_head_kondisi',
            'hidrotest.detail_kondisi as hidrotest_kondisi',
        )
        ->get();
    // Generate PDF
    $pdf = Pdf::loadView('pdf.apar_report', [
        'agenda' => $data,
        'apar' => $apar
    ])->setPaper('A3', 'fertical');

    // Simpan file ke storage (public path)
    $filename = 'Laporan_Inspeksi_APAR_' . Str::slug($data->no_jadwal) . '.pdf';
    $path = 'pdf_reports/' . $filename;

    Storage::disk('public')->put($path, $pdf->output());

    // Buat URL
    $url = asset('storage/' . $path);

    return response()->json([
        'message' => 'Laporan berhasil dibuat',
        'download_url' => $url,
    ]);
}
//  public function updateStatusInspection (Request $request)
//  {
//     $validator = Validator::make($request->all(), [
//         'id_jadwal'          => 'required',
//     ]);
//    $data = DB::table('tabel_header_jadwal')
//     ->where('tabel_header_jadwal.id', $request->id_jadwal)
//     ->leftJoin('users as pic', 'pic.id', '=', 'tabel_header_jadwal.inspeksi_pic')
//     ->leftJoin('users as creator', 'creator.id', '=', 'tabel_header_jadwal.created_by')
//     ->select(
//         'tabel_header_jadwal.*',
//         'pic.name as inspection_name',
//         'creator.name as created_name'
//     )
//     ->first();
//     $apar = DB::table('tabel_inspection')->where("no_jadwal", $data->no_jadwal)
//     ->leftJoin('users as qc_name', 'qc_name.id', '=', 'tabel_inspection.qc')
//     ->select(
//         'tabel_inspection.*',
//         'qc_name.name as qc_name',
//     )
//     ->get();
//     return response()->json([
//         'message' => 'list inspeksi',
//         'detail_agenda' => $data,
//         'list_apar' =>$apar
//     ], 201);
//  }
}
