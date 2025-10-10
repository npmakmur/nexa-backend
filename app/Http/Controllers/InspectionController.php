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
      2, 4, 8, 12, 15
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
    return 'rusak';
 }
 public function inspectionApar (Request $request)
 {
    $validator = Validator::make($request->all(), [
        'id_jadwal'          => 'required|exists:tabel_header_jadwal,id',
        'kode_barang'        => 'required|exists:tabel_produk,kode_barang',
        'id_inspection'      => 'nullable|numeric',
        'pressure'           => 'required|numeric',
        'expired'            => 'required|numeric',
        'selang'             => 'required|numeric',
        'head_valve'         => 'required|numeric',
        'korosi'             => 'required|numeric',
        'pressure_img'       => 'nullable|image|max:2048',
        'expired_img'        => 'nullable|image|max:2048',
        'selang_img'         => 'nullable|image|max:2048',
        'head_valve_img'     => 'nullable|image|max:2048',
        'korosi_img'         => 'nullable|image|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validasi gagal.',
            'errors'  => $validator->errors()
        ], 422);
    }

    $now = Carbon::now();
    $userId = auth()->id();
    $userKodeCustomer = auth()->user()->kode_customer;

    // 2. Ambil data terkait yang penting
    $schedule = DB::table('tabel_header_jadwal')->where("id", $request->id_jadwal)->first();
    $product = DB::table('tabel_produk')->where("kode_barang", $request->kode_barang)->first();


    // Periksa apakah jadwal dan produk ada
    if (!$schedule || !$product) {
        return response()->json(['message' => 'Jadwal atau Produk tidak ditemukan.'], 404);
    }

    // Periksa status jadwal
    if ($schedule->status === "Selesai") {
        return response()->json([
            'message' => 'Inspeksi sudah selesai untuk jadwal ini.',
        ], 400);
    } elseif ($schedule->status === "Dijeda") {
        return response()->json([
            'message' => 'Inspeksi masih dijeda.',
        ], 400);
    }

    $customer = DB::table('tabel_master_customer')
                    ->where('kode_customer', $schedule->kode_customer)
                    ->first();

    if (!$customer) {
        return response()->json(['message' => 'Pelanggan tidak ditemukan untuk jadwal ini.'], 404);
    }

    // 3. Proses ringkasan bagian yang rusak (partbroken_summary)
    $idRusak = [2, 4, 8, 12, 15];
    $inspectionAnswers = [
        'pressure'   => (int) $request->pressure,
        'expired'    => (int) $request->expired,
        'selang'     => (int) $request->selang,
        'head_valve' => (int) $request->head_valve,
        'korosi'     => (int) $request->korosi,
    ];

    foreach ($inspectionAnswers as $field => $value) {
        if (in_array($value, $idRusak)) {
            DB::table('partbroken_summary')->updateOrInsert(
                [
                    'nama_detail_activity' => $field,
                    'kode_customer'        => $userKodeCustomer,
                ],
                [
                    'total_rusak'  => DB::raw('total_rusak + 1'),
                    'last_user_id' => $userId,
                    'updated_at'   => $now,
                    'created_at'   => $now, // Tambahkan ini agar `created_at` diatur pada insert pertama
                ]
            );
        }
    }

    // 4. Tangani unggahan gambar
    $imagePaths = [];
    $imageFields = [
        'pressure_img',
        'expired_img',
        'selang_img',
        'head_valve_img',
        'korosi_img',
    ];

    foreach ($imageFields as $field) {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $timestamp = $now->format('Ymd_His');
            $random = Str::random(5);
            $filename = "{$field}_{$timestamp}_{$random}." . $file->getClientOriginalExtension();
            $imagePaths[$field] = $file->storeAs('apar', $filename, 'public');
        } else {
            $imagePaths[$field] = null;
        }
    }

    // 5. Tentukan status APAR
    $status = $this->getStatusAparById($inspectionAnswers);

    // 6. Siapkan data inspeksi umum
    $inspectionData = [
        "kode_customer"      => $schedule->kode_customer,
        "nama_customer"      => $customer->nama_customer,
        "kode_barang"        => $product->kode_barang,
        "tanggal_cek"        => $now->format('Y-m-d'),
        "lokasi"             => $product->lokasi,
        "barcode"            => $product->barcode,
        "status"             => $status,
        "brand"              => $product->brand,
        "type"               => $product->type,
        "media"              => $product->media,
        "kapasitas"          => $product->kapasitas,
        "pressure"           => $request->pressure,
        "pressure_img"       => $imagePaths['pressure_img'],
        "expired"            => $request->expired,
        "expired_img"        => $imagePaths['expired_img'],
        "hose"             => $request->selang,
        "hose_img"         => $imagePaths['selang_img'],
        "head_valve"         => $request->head_valve,
        "head_valve_img"     => $imagePaths['head_valve_img'],
        "korosi"             => $request->korosi,
        "korosi_img"         => $imagePaths['korosi_img'],
        "qc"                 => $userId,
        "updated_at"         => $now,
        'created_at'         => $now, // Tambahkan ini untuk insert
    ];

    // 7. Masukkan atau Perbarui `tabel_inspection`
    $inspectionId = $request->input('id_inspection');
    $activityMessage = '';

    if ($inspectionId) {
        // Perbarui catatan inspeksi yang sudah ada
        $updated = DB::table('tabel_inspection')
                    ->where('id_inspection', $inspectionId)
                    ->update($inspectionData);

        if ($updated) {
            $activityMessage = 'Memperbarui Inspeksi dengan ID inspeksi ' . $inspectionId;
        } else {
            // Logika ini diperbaiki: Jika id_inspection tidak ada, itu adalah insert
            $inspectionData['no_jadwal'] = $schedule->no_jadwal;
            DB::table('tabel_inspection')->insert($inspectionData);
            $activityMessage = 'Membuat Inspeksi baru karena ID inspeksi ' . $inspectionId . ' tidak ditemukan.';
        }
    } else {
        // Buat catatan inspeksi baru
        $cek_apar_inspected =  DB::table('tabel_inspection')->where("no_jadwal", $schedule->no_jadwal)->where("kode_barang", $product->kode_barang,)->first();
        if ($cek_apar_inspected) {
            return response()->json([
                'message' => 'apar suda di inspeksi.',
            ], 400);
        }
        $inspectionData['no_jadwal'] = $schedule->no_jadwal;
        DB::table('tabel_inspection')->insert($inspectionData);
        $activityMessage = 'Inspeksi APAR baru: ' . $request->kode_barang;

        // Hanya perbarui status jadwal pada inspeksi PERTAMA untuk jadwal ini
        if ($schedule->status === 'Belum dikerjakan' || $schedule->status === 'Menunggu') {
            DB::table('tabel_header_jadwal')
                ->where('id', $schedule->id)
                ->update([
                    'status'               => 'On progress',
                    'tgl_mulai_sebenarnya' => $now->format('Y-m-d'),
                    'updated_at'           => $now,
                ]);
        }
    }

    // Hitung ulang jumlah APAR yang telah diinspeksi untuk jadwal ini
    DB::table('tabel_header_jadwal')
        ->where('id', $schedule->id)
        ->update([
            'jumlah_apar' => DB::table('tabel_inspection')->where("no_jadwal", $schedule->no_jadwal)->count(),
            'updated_at'  => $now,
        ]);

    // 8. Perbarui status Produk dan tanggal inspeksi terakhir
    DB::table('tabel_produk')->where("kode_barang", $request->kode_barang)->update([
        "pressure"        => $request->pressure,
        "expired"         => $request->expired,
        "hose"          => $request->selang,
        "head_valve"      => $request->head_valve,
        "korosi"          => $request->korosi,
        "status"          => $status,
        "last_inspection" => $now,
        "updated_at"      => $now, // Tambahkan updated_at
    ]);

    // 9. Catat aktivitas
    DB::table('tabel_aktivitas')->insert([
        'aktivitas_by'   => $userId,
        'aktivitas_name' => $activityMessage,
        'tanggal'        => $now,
        'created_by'     => $userId,
        'created_at'     => $now,
    ]);

    return response()->json([
        'message' => 'Inspeksi APAR berhasil.',
        'status'  => $status,
    ], 201);
 }
 public function listInspection (Request $request)
 {
   $list = TabelHeaderJadwal::where("tabel_header_jadwal.kode_customer", auth()->user()->kode_customer)
    ->leftJoin('users', 'users.id', '=', 'tabel_header_jadwal.inspeksi_pic')
    ->select('tabel_header_jadwal.*', 'users.name as inspection_name')
    ->orderBy("id", "desc")
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
    ->leftJoin('tabel_detail_kondisi as pressure_kondisi', 'tabel_inspection.pressure', '=', 'pressure_kondisi.id')
    ->leftJoin('tabel_detail_kondisi as hose_kondisi', 'tabel_inspection.hose', '=', 'hose_kondisi.id')
    ->leftJoin('tabel_detail_kondisi as head_valve_kondisi', 'tabel_inspection.head_valve', '=', 'head_valve_kondisi.id')
    ->leftJoin('tabel_detail_kondisi as korosi_kondisi', 'tabel_inspection.korosi', '=', 'korosi_kondisi.id')
    ->leftJoin('tabel_detail_kondisi as expired_kondisi', 'tabel_inspection.expired', '=', 'expired_kondisi.id')
    ->select(
        'tabel_inspection.*',
        'qc_name.name as qc_name',
        'pressure_kondisi.detail_kondisi as detail_pressure',
        'hose_kondisi.detail_kondisi as detail_hose',
        'head_valve_kondisi.detail_kondisi as detail_head_valve',
        'korosi_kondisi.detail_kondisi as detail_korosi',
        'expired_kondisi.detail_kondisi as detail_expired'
    )
    ->get();
    return response()->json([
        'message' => 'detail inspeksi agenda',
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
    $apar = Product::where("kode_customer", auth()->user()->kode_customer)->orderBy("id", "desc")->get();
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
    $inspection = DB::table("tabel_inspection")
    ->leftJoin("tabel_produk", "tabel_inspection.kode_barang", "=", "tabel_produk.kode_barang")
    ->leftJoin("users", "tabel_inspection.qc", "=", "users.id")
    ->where("tabel_inspection.no_jadwal", $schedule->no_jadwal)
    ->select(
        "tabel_inspection.id_inspection",
        "tabel_inspection.no_jadwal",
        "tabel_inspection.tanggal_cek",
        "users.name as inpection_name",
        "tabel_inspection.status",
        "tabel_produk.*",
    )
    ->orderBy("id_inspection", "desc")
    ->get();
    return response()->json([
        'message' => 'list inspeksi',
        'data_list_apar_inspected' => $inspection,
    ], 201);
 }
public function generateAparReport(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id_jadwal' => 'required|exists:tabel_header_jadwal,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
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

    if (!$data) {
        return response()->json(['error' => 'Data tidak ditemukan'], 404);
    }

    $apar = DB::table('tabel_inspection')
        ->where("no_jadwal", $data->no_jadwal)
        ->leftJoin('users as qc_name', 'qc_name.id', '=', 'tabel_inspection.qc')
        ->leftJoin('tabel_detail_kondisi as pressure_kondisi', 'tabel_inspection.pressure', '=', 'pressure_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as hose_kondisi', 'tabel_inspection.hose', '=', 'hose_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as head_valve_kondisi', 'tabel_inspection.head_valve', '=', 'head_valve_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as korosi_kondisi', 'tabel_inspection.korosi', '=', 'korosi_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as expired_kondisi', 'tabel_inspection.expired', '=', 'expired_kondisi.id')
        ->select(
            'tabel_inspection.*',
            'qc_name.name as qc_name',
            'pressure_kondisi.detail_kondisi as detail_pressure',
            'hose_kondisi.detail_kondisi as detail_hose',
            'head_valve_kondisi.detail_kondisi as detail_head_valve',
            'korosi_kondisi.detail_kondisi as detail_korosi',
            'expired_kondisi.detail_kondisi as detail_expired'
        )
        ->get();
    // Generate PDF
    $kop = DB::table("kop_surat")->where("type","inspection")->where("aktif","aktif")->where("kode_customer", auth()->user()->kode_customer)->first();
    $pdf = Pdf::loadView('pdf.apar_report', [
        'agenda' => $data,
        'apar' => $apar,
        'kop' => $kop
    ])->setPaper('a3', 'portrait');

    // Simpan langsung ke storage/app/public/reports
    $fileName = 'Laporan_Inspeksi_APAR_A1' . str_replace('/', '-', $data->no_jadwal) . '.pdf';
    $filePath = 'reports/' . $fileName;
    Storage::disk('public')->put($filePath, $pdf->output());

    // Buat URL publik untuk unduh
    $url = asset('storage/' . $filePath);

    return response()->json([
        'message' => 'Laporan berhasil dibuat',
        'download_url' =>  url('/api/inspection/download/' . $fileName),

    ]);
}
public function downloadAparReport($file)
{
    $filePath = storage_path('app/public/reports/' . $file);
    if (!file_exists($filePath)) {
        return response()->json([
            'status' => false,
            'message' => 'File tidak ditemukan'
        ], 404);
    }

    return response()->download($filePath, $file, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $file . '"'
    ]);
}

public function precetagePartBroken (Request $request)
{
    $partBrokenCount = DB::table('partbroken_summary')
    ->where("kode_customer", auth()->user()->kode_customer)
    ->sum('total_rusak');

    $partBroken = DB::table('partbroken_summary')
    ->where('kode_customer', auth()->user()->kode_customer)
    ->get()
    ->map(function ($data) use ($partBrokenCount) {
        $raw_percentage = $partBrokenCount > 0
            ? ($data->total_rusak / $partBrokenCount) * 100
            : 0;

        // Simpan nilai bulat & desimal
        $data->persentase_rusak = floor($raw_percentage);
        return $data;
    });

// Hitung total
    $total = $partBroken->sum('persentase_rusak');

// Kalau masih kurang dari 100, tambahkan ke yang fraction terbesar
    if ($total < 100) {
        $difference = 100 - $total;
        $partBroken = $partBroken->sortByDesc('fraction')->values();
        for ($i = 0; $i < $difference; $i++) {
            if (isset($partBroken[$i])) $partBroken[$i]->persentase_rusak++;
        }
    }
// Kalau lebih dari 100, kurangi dari yang fraction terkecil
    elseif ($total > 100) {
        $difference = $total - 100;
        $partBroken = $partBroken->sortBy('fraction')->values();
        for ($i = 0; $i < $difference; $i++) {
            if (isset($partBroken[$i])) $partBroken[$i]->persentase_rusak--;
        }
    }

    return response()->json([
        'message' => 'Presentasi part sering rusak',
        'data' => $partBroken,
    ],200);
}
public function detailInspectionApar(Request $request)
{
    $apar = DB::table('tabel_inspection')->where("id_inspection", $request->id_inspection)
        ->leftJoin('tabel_produk as produk', 'produk.kode_barang', '=', 'tabel_inspection.kode_barang')
        ->leftJoin('tabel_gedung as location', 'location.id', '=', 'produk.lokasi')
        ->leftJoin('tabel_titik_penempatan as preposition_place', 'preposition_place.id', '=', 'produk.titik_penempatan_id')
        ->leftJoin('users as qc_name', 'qc_name.id', '=', 'tabel_inspection.qc')
        ->leftJoin('tabel_detail_kondisi as pressure_kondisi', 'tabel_inspection.pressure', '=', 'pressure_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as hose_kondisi', 'tabel_inspection.hose', '=', 'hose_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as head_valve_kondisi', 'tabel_inspection.head_valve', '=', 'head_valve_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as korosi_kondisi', 'tabel_inspection.korosi', '=', 'korosi_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as expired_kondisi', 'tabel_inspection.expired', '=', 'expired_kondisi.id')
        ->select(
            'tabel_inspection.id_inspection',
            'produk.barcode',
            'produk.brand',
            'produk.type',
            'produk.media',
            'produk.kapasitas',
            'produk.tgl_produksi',
            'produk.berat',
            'location.nama_gedung',
            'preposition_place.nama_titik',
            'tabel_inspection.no_jadwal',
            'tabel_inspection.pressure_img',
            'tabel_inspection.hose_img',
            'tabel_inspection.head_valve_img',
            'tabel_inspection.expired_img',
            'tabel_inspection.korosi_img',
            'qc_name.name as qc_name',
            'pressure_kondisi.detail_kondisi as detail_pressure',
            'hose_kondisi.detail_kondisi as detail_hose',
            'head_valve_kondisi.detail_kondisi as detail_head_valve',
            'korosi_kondisi.detail_kondisi as detail_korosi',
            'expired_kondisi.detail_kondisi as detail_expired'
        )
        ->get();

    // mapping asset() untuk kolom image
    $apar = $apar->map(function ($item) {
        $item->pressure_img = $item->pressure_img ? asset('storage/' . $item->pressure_img) : null;
        $item->hose_img = $item->hose_img ? asset('storage/' . $item->hose_img) : null;
        $item->head_valve_img = $item->head_valve_img ? asset('storage/' . $item->head_valve_img) : null;
        $item->expired_img = $item->expired_img ? asset('storage/' . $item->expired_img) : null;
        $item->korosi_img = $item->korosi_img ? asset('storage/' . $item->korosi_img) : null;
        return $item;
    });

    return response()->json([
        'message' => 'detail inspeksi',
        'list_apar' => $apar
    ], 201);
}

public function proggress (Request $request)
{
    $validator = Validator::make($request->all(), [
        'id_jadwal'          => 'required',
    ]);
    $schedule = DB::table("tabel_header_jadwal")->where("id", $request->id_jadwal)->first();
    $inspection = DB::table("tabel_inspection")->where("no_jadwal", $schedule->no_jadwal)->pluck("kode_barang");
    $apar_inspected = Product::WhereIn("kode_barang", $inspection)->get();
    $count_apar_inspected = count($apar_inspected);
    $apar = Product::where("kode_customer", auth()->user()->kode_customer)->get();
    $filteredApar = $apar->reject(function ($item) use ($inspection) {
        return $inspection->contains($item->kode_barang);
    });
    $count_apar_uninspected = count($filteredApar);
    return response()->json([
        'message' => 'proggress inspection',
        'proggress_inspected' => $count_apar_inspected,
        'proggress_uninspected' => $count_apar_uninspected
    ], 201);

}
public function deleteAparInspection (Request $request)
{
    $validator = Validator::make($request->all(), [
        'id_inspection' => "required"
    ]);
    $apar_inspection = DB::table("tabel_inspection")
    ->where("id_inspection", $request->id_inspection)
    ->first();
    if ($apar_inspection) {
         DB::table("tabel_inspection")
        ->where("id_inspection", $request->id_inspection)
        ->delete();
         return response()->json([
            'status'  => 'success',
            'message' => 'Data APAR berhasil dihapus'
        ],201);
        }else {
        return response()->json([
            'status'  => 'error',
            'message' => 'APAR tidak ditemukan'
        ], 404);
    }
}
public function lastInspection()
{
    $id = auth()->user()->id;
    $inspection = DB::table("tabel_inspection")->where("qc",$id)
    ->orderBy("id_inspection", "desc") // Urutkan dari ID terbesar ke terkecil
    ->limit(3) // Ambil 3 data teratas
    ->get()
    ->map(function($item){
        $produk = DB::table("tabel_produk")->where("kode_barang", $item->kode_barang)->first();
        $location = DB::table("tabel_gedung")->where("id",$produk->lokasi)->first();
        $titikPenempatan = DB::table("tabel_titik_penempatan")->where("id",$produk->titik_penempatan_id)->first();
        $item->lokasi = $location->nama_gedung ?? null;
        $item->location_point = $titikPenempatan->nama_titik ?? null;
        return $item;
    });
    return response()->json([
        'message' => 'last inspeksi',
        'list_inspection' =>$inspection
    ], 201);

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
