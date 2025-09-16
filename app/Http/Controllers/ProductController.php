<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\Product;
use App\Models\TabelHeaderJadwal;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Colors\Rgb\Channels\Red;
use Symfony\Component\Uid\NilUlid;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'deskripsi' => 'nullable|string',
            'brand' => 'required|string|max:191',
            'type' => 'nullable|string|max:191',
            'media' => 'required|string|max:191',
            'kapasitas' => 'required|string|max:191',
            'jumlah' => 'required|integer|min:1',
            'tanggal_produksi'     => 'required|date',
            'tanggal_kadaluarsa'   => 'required|date|after_or_equal:tanggal_produksi',
        ]);

        $jumlah = $request->jumlah;
        $products = [];
        $qrCodes = [];

        // Ambil base URL QR dari env, fallback ke APP_URL/product
        $baseUrl = env('QR_BASE_URL', config('app.url') . '/product');

        for ($i = 0; $i < $jumlah; $i++) {
            $product = Product::create([
                'kode_barang' => 'temporary',
                'barcode' => 'temporary',
                'deskripsi' => $request->deskripsi,
                'brand' => $request->brand,
                'type' => $request->type,
                'media' => $request->media,
                'kapasitas' => $request->kapasitas,
                'tgl_produksi' => $request->tanggal_produksi,
                'tgl_kadaluarsa' => $request->tanggal_kadaluarsa,
                'garansi' => $request->garansi,
                'lokasi' => $request->lokasi,
                'kode_customer' => auth()->user()->kode_customer,
            ]);

            $kodeUnik = 'PROD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT);
            $linkQRCode = $baseUrl . '/' . $kodeUnik;

            // Generate PNG QR code dari link
            $qrImage = Builder::create()
                ->writer(new PngWriter())
                ->data($linkQRCode)
                ->size(300)
                ->margin(10)
                ->build();

            $qrPath = 'qrcodes/' . $kodeUnik . '.png';
            Storage::disk('public')->put($qrPath, $qrImage->getString());

            // Update barcode dan kode_barang
            $product->update([
                'barcode' => $linkQRCode,
                'kode_barang' => $kodeUnik,
            ]);

            $products[] = $product;

            $qrCodes[] = [
                'barcode' => $kodeUnik,
                'link' => $linkQRCode,
                'path' => $qrPath,
            ];
        }

        // Buat PDF berisi semua QR code
        $pdf = Pdf::loadView('pdf.qrcodes_pdf', ['qrCodes' => $qrCodes]);
        $pdfFileName = 'all_qr_codes_' . now()->format('Ymd_His') . '.pdf';
        $pdfPath = 'qrcodes_pdf/' . $pdfFileName;
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // Simpan aktivitas
        Aktivitas::create([
            'aktivitas_by' => auth()->user()->id,
            'aktivitas_name' => 'Menambahkan apar',
            'tanggal' => now(),
            'created_by' => auth()->user()->id,
            'created_at' => now(),
        ]);
        DB::table('tabel_add_qr')->insert([
            "date" => now(),
            "count_qr" => count($products),
            "kode_customer" => auth()->user()->kode_customer,
            "path_qr" => $pdfPath,
            "created_at" => now()
        ]);
        return response()->json([
            'message' => 'Produk berhasil disimpan dan QR code berupa link disertakan dalam PDF.',
            'data' => $products,
            'pdf_download_url' => url(Storage::url($pdfPath)),
        ]);
    }
    public function storeSuperAdmin(Request $request)
    {
        $request->validate([
            'deskripsi' => 'nullable|string',
            'brand' => 'required|string|max:191',
            'type' => 'nullable|string|max:191',
            'media' => 'required|string|max:191',
            'kapasitas' => 'required|string|max:191',
            'jumlah' => 'required|integer|min:1',
            'tanggal_produksi'     => 'required|date',
            'tanggal_kadaluarsa'   => 'required|date|after_or_equal:tanggal_produksi',
        ]);

        $jumlah = $request->jumlah;
        $products = [];
        $qrCodes = [];

        // Ambil base URL QR dari env, fallback ke APP_URL/product
        $baseUrl = env('QR_BASE_URL', config('app.url') . '/product');
        $batchNumber = "BATCH-" . date('YmdHis') . rand(100,999);

        for ($i = 0; $i < $jumlah; $i++) {
            $product = Product::create([
                'kode_barang' => 'temporary',
                'barcode' => 'temporary',
                'deskripsi' => $request->deskripsi,
                'brand' => $request->brand,
                'type' => $request->type,
                'media' => $request->media,
                'kapasitas' => $request->kapasitas,
                'tgl_produksi' => $request->tanggal_produksi,
                'tgl_kadaluarsa' => $request->tanggal_kadaluarsa,
                'garansi' => $request->garansi,
                'lokasi' => $request->lokasi,
                'batch' => $batchNumber,
                'kode_customer' => $request->kode_customer ?? null,
            ]);

            $kodeUnik = 'PROD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT);
            $linkQRCode = $baseUrl . '/' . $kodeUnik;

            // Generate PNG QR code dari link
            $qrImage = Builder::create()
                ->writer(new PngWriter())
                ->data($linkQRCode)
                ->size(300)
                ->margin(10)
                ->build();

            $qrPath = 'qrcodes/' . $kodeUnik . '.png';
            Storage::disk('public')->put($qrPath, $qrImage->getString());

            // Update barcode dan kode_barang
            
            $product->update([
                'barcode' => $linkQRCode,
                'kode_barang' => $kodeUnik,
            ]);

            $products[] = $product;

            $qrCodes[] = [
                'barcode' => $kodeUnik,
                'link' => $linkQRCode,
                'path' => $qrPath,
            ];
        }

        // Buat PDF berisi semua QR code
        $pdf = Pdf::loadView('pdf.qrcodes_pdf', ['qrCodes' => $qrCodes]);
        $pdfFileName = 'all_qr_codes_' . now()->format('Ymd_His') . '.pdf';
        $pdfPath = 'qrcodes_pdf/' . $pdfFileName;
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // Simpan aktivitas
        Aktivitas::create([
            'aktivitas_by' => auth()->user()->id,
            'aktivitas_name' => 'Menambahkan apar',
            'tanggal' => now(),
            'created_by' => auth()->user()->id,
            'created_at' => now(),
        ]);

        DB::table('tabel_add_qr')->insert([
            "date" => now(),
            "count_qr" => count($products),
            "kode_customer" =>  $request->kode_customer ?? null,
            "path_qr" => $pdfPath,
            'batch' => $batchNumber,
            "created_at" => now()
        ]);
        return response()->json([
            'message' => 'Produk berhasil disimpan dan QR code berupa link disertakan dalam PDF.',
            'data' => $products,
            'pdf_download_url' => url(Storage::url($pdfPath)),
        ]);
    }
    public function getAparSuperAdmin(Request $request)
    {
        $apar = DB::table("tabel_add_qr")
                ->whereNotNull("batch")
                ->orderBy("id", "desc")
                ->get()
                ->map(function($data){
                     $list_apar = Product::where("batch", $data->batch)
                    ->get()
                    ->map(function($item){
                        $lokasi = DB::table("tabel_gedung")->where("id", $item->lokasi)->first();
                        $lokasiPoint = DB::table("tabel_titik_penempatan")->where("id", $item->titik_penempatan_id)->first();
                        
                        $item->lokasi = $lokasi->nama_gedung ?? null;
                        $item->titik_penempatan_id = $lokasiPoint->nama_titik ?? null;
                        return $item;
                    });
                    $fileName = base64_encode($data->path_qr);
                    $data->download_qr_url =  url('/api/product/download/' . $fileName);
                    $data->count_apar = count($list_apar);
                    $data->list_apar = $list_apar;
                    return $data;
                });

        return response()->json([
            'message' => 'List APAR Super Admin.',
            'list_apar' => $apar,
        ], 200);
    }
    public function updateCustomerCodeByBatch(Request $request)
    {
        $request->validate([
            'batch' => 'required|string',
            'kode_customer' => 'required|string',
        ]);

        $batchToUpdate = $request->input('batch');
        $newCustomerCode = $request->input('kode_customer');

        // Ambil data APAR berdasarkan batch
        $apar = Product::where('batch', $batchToUpdate)
                        ->get()
                        ->groupBy('batch');

        $affectedRows = Product::where('batch', $batchToUpdate)
                               ->update(['kode_customer' => $newCustomerCode]);


        return response()->json([
            'message' => 'Kode customer berhasil diperbarui untuk ' . $affectedRows . ' record.',
            'list_apar' => $affectedRows,
        ], 200);
    }
    public function count_apar (Request $request)
    {
        $apar = Product::where("kode_customer", auth()->user()->kode_customer)->get();
        $count_apar = count($apar);
          return response()->json([
            'message' => 'Jumlah apar berhasil didapatkan.',
            'data' => $count_apar,
        ]);
    }
    public function list_apar(Request $request)
    {
        $query = Product::where("kode_customer", auth()->user()->kode_customer) ->orderBy("id", "desc");

        // Filter berdasarkan lokasi (lokasi)
        if ($request->filled('lokasi')) {
            $query->where('lokasi', $request->lokasi);
        }

        // Filter berdasarkan pencarian nama atau kode APAR (LIKE)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                ->orWhere('media', 'like', "%{$search}%");
            });
        }

        $apar = $query->get()->map(function($item){
            $lokasi = DB::table("tabel_gedung")->where("id", $item->lokasi)->first();
            $lokasiPoint = DB::table("tabel_titik_penempatan")->where("id",$item->titik_penempatan_id)->first();
            $item->lokasi = $lokasi->nama_gedung ?? null;
            $item->titik_penempatan_id = $lokasiPoint->nama_titik ?? null;
            return $item;
        });

        return response()->json([
            'message' => 'List APAR berhasil didapatkan.',
            'list_apar' => $apar,
        ], 200);
    }

    public function update(Request $request)
    {
        $messages = [
            'id.required' => 'Field id wajib diisi.',
            'id.exists' => 'Produk dengan id tersebut tidak ditemukan.',
            'brand.required' => 'Field brand wajib diisi.',
            'brand.string' => 'Field brand harus berupa teks.',
            'brand.max' => 'Field brand maksimal 191 karakter.',
            'type.string' => 'Field type harus berupa teks.',
            'type.max' => 'Field type maksimal 191 karakter.',
            'media.required' => 'Field media wajib diisi.',
            'media.string' => 'Field media harus berupa teks.',
            'media.max' => 'Field media maksimal 191 karakter.',
            'kapasitas.required' => 'Field kapasitas wajib diisi.',
            'kapasitas.string' => 'Field kapasitas harus berupa teks.',
            'kapasitas.max' => 'Field kapasitas maksimal 191 karakter.',
            'tanggal_kadaluarsa.required' => 'Field tanggal kadaluarsa wajib diisi.',
            'tanggal_kadaluarsa.date' => 'Field tanggal kadaluarsa harus berupa tanggal.',
            'tanggal_kadaluarsa.after_or_equal' => 'Tanggal kadaluarsa harus setelah atau sama dengan tanggal produksi.',
            'garansi.string' => 'Field garansi harus berupa teks.',
            'garansi.max' => 'Field garansi maksimal 191 karakter.',
            'lokasi.required' => 'Field lokasi wajib diisi.',
            'lokasi.exists' => 'Lokasi tidak valid.',
            'titik_penempatan_id.required' => 'Field titik penempatan wajib diisi.',
        ];
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tabel_produk,id',
            'brand' => 'sometimes|string|max:191',
            'type' => 'sometimes|string|max:191',
            'media' => 'sometimes|string|max:191',
            'kapasitas' => 'sometimes|string|max:191',
            'tgl_produksi' => 'sometimes|date',
            'tgl_kadaluarsa' => 'sometimes|date|after_or_equal:tgl_produksi',
            'garansi' => 'sometimes|string|max:191',
            'lokasi' => 'sometimes|exists:tabel_gedung,id',
            'titik_penempatan_id' => 'sometimes'
        ], $messages);        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        
        $product = Product::find($request->id);
        $data = $request->except('id');
        $data['updated_at'] = now();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada perubahan atau produk tidak ditemukan.',
            ], 400);
        }
        $product->update($data);

        // Catat aktivitas
        $now = now();
        Aktivitas::create([
            'aktivitas_by' => auth()->id(),
            'aktivitas_name' => 'Mengupdate apar',
            'tanggal' => $now,
            'created_by' => auth()->id(),
            'created_at' => $now,
        ]);

        // Response sukses
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
        ], 200);
    }
    public function detail_apar(Request $request)
    {
        $product = Product::select(
            'tabel_produk.*', 
            'pressure_kondisi.detail_kondisi as detail_pressure',
            'hose_kondisi.detail_kondisi as detail_hose',
            'head_valve_kondisi.detail_kondisi as detail_head_valve',
            'korosi_kondisi.detail_kondisi as detail_korosi',
            'expired_kondisi.detail_kondisi as detail_expired',
            'lokasi_name.nama_gedung as building_name',
            'placement_point.nama_titik as placement_point_name',
        )
        ->leftJoin('tabel_detail_kondisi as pressure_kondisi', 'tabel_produk.pressure', '=', 'pressure_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as hose_kondisi', 'tabel_produk.hose', '=', 'hose_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as head_valve_kondisi', 'tabel_produk.head_valve', '=', 'head_valve_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as korosi_kondisi', 'tabel_produk.korosi', '=', 'korosi_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as expired_kondisi', 'tabel_produk.expired', '=', 'expired_kondisi.id')
        ->leftJoin('tabel_gedung as lokasi_name', 'tabel_produk.lokasi', '=', 'lokasi_name.id')
        ->leftJoin('tabel_titik_penempatan as placement_point', 'tabel_produk.titik_penempatan_id', '=', 'placement_point.id')
        ->where('kode_barang', $request->id_barang)
        ->first();

        $history = DB::table("tabel_inspection")
        ->select(
            'tabel_inspection.*', 
            'pressure_kondisi.detail_kondisi as detail_pressure',
            'hose_kondisi.detail_kondisi as detail_hose',
            'head_valve_kondisi.detail_kondisi as detail_head_valve',
            'korosi_kondisi.detail_kondisi as detail_korosi',
            'expired_kondisi.detail_kondisi as detail_expired'
        )
        ->leftJoin('tabel_detail_kondisi as pressure_kondisi', 'tabel_inspection.pressure', '=', 'pressure_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as hose_kondisi', 'tabel_inspection.hose', '=', 'hose_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as head_valve_kondisi', 'tabel_inspection.head_valve', '=', 'head_valve_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as korosi_kondisi', 'tabel_inspection.korosi', '=', 'korosi_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as expired_kondisi', 'tabel_inspection.expired', '=', 'expired_kondisi.id')
        ->where("tabel_inspection.kode_barang", $product->kode_barang)
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data' => $product,
            'history' => $history
        ], 200);
    }
    public function apar_done_permount (Request $request)
    {
       $now = Carbon::now();
        $apar = Product::where("kode_customer", auth()->user()->kode_customer)->get();
        $count_apar = count($apar);

        // Ambil header jadwal inspeksi bulan ini
        $list = TabelHeaderJadwal::where("tabel_header_jadwal.kode_customer", auth()->user()->kode_customer)
            ->whereMonth('tabel_header_jadwal.tgl_mulai', $now->month)
            ->whereYear('tabel_header_jadwal.tgl_mulai', $now->year)
            ->leftJoin('users', 'users.id', '=', 'tabel_header_jadwal.inspeksi_pic')
            ->select('tabel_header_jadwal.*', 'users.name as inspection_name')
            ->first();

        if (!$list) {
            return response()->json([
                'message' => 'Belum ada jadwal inspeksi pada bulan ' . $now->translatedFormat('F') . '.',
                'data' => (object)[
                    'persentase' => '0%',
                    'count_apar' => $count_apar
                ]
            ], 200);
        }

        // Jika ada jadwal, ambil data inspeksi
        $inspection = DB::table('tabel_inspection')
            ->where("no_jadwal", $list->no_jadwal)
            ->get()
            ->groupBy("kode_barang");

        $apar_inspection = count($inspection);

        // Hitung persentase
        $persentase = $count_apar > 0 ? ($apar_inspection / $count_apar) * 100 : 0;

        return response()->json([
            'message' => 'Jumlah APAR yang sudah diinspeksi pada bulan ' . $now->translatedFormat('F') . '.',
            'data' => round($persentase, 2) . '%', 200
        ]);
    }
    public function list_qr (Request $request)
    {
        $data_qr = DB::table("tabel_add_qr")
        ->where("kode_customer", auth()->user()->kode_customer)
         ->orderBy("id", "desc")
        ->get()
        ->map(function($data){
            $path_qr = url(Storage::url($data->path_qr));
            $data->url_qr = $path_qr;
            return $data;
        });

        return response()->json([
            'message' => 'list data qr',
            'data' => $data_qr
        ]);

    }
    public function listQrSuperAdmin (Request $request)
    {
        $data_qr = DB::table("tabel_add_qr")
        ->where("batch", $request->batch)
         ->orderBy("id", "desc")
        ->first();
        $fileName = base64_encode($data_qr->path_qr);
        return response()->json([
            'message' => 'list data qr',
            'data' => $data_qr,
            'download_url' =>  url('/api/product/download/' . $fileName)
        ]);

    }
    public function download($file)
    {
        $file = base64_decode($file);
        $filename = basename($file);
        $filePath = storage_path('app/public/' . $file);

        if (!file_exists($filePath)) {
            return response()->json([
                'status' => false,
                'message' => 'File tidak ditemukan'
            ], 404);
        }

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    public function countAparBroken (Request $request)
    {
        $apar = DB::table("tabel_produk")
            ->where("kode_customer", auth()->user()->kode_customer)
            ->get()
            ->map(function($data){
                $brokenApar = DB::table("tabel_inspection")
                    ->where("kode_barang", $data->kode_barang)
                    ->orderByDesc("id_inspection")
                    ->first();

                $data->last_inspection = $brokenApar; // simpan object inspection
                return $data;
            })
            ->filter(function($data) {
                return $data->last_inspection 
                    && $data->last_inspection->status === 'rusak';
            })
            ->count();

        return response()->json([
            'message' => 'jumlah apar rusak',
            'data' => [
                "apar_broken" => $apar
            ]
        ], 200);
    }
    public function listAparBroken (Request $request)
    {
        $apar = DB::table("tabel_produk")
        ->where("kode_customer", auth()->user()->kode_customer)
        ->get()
        ->map(function($data) {
            $brokenApar = DB::table("tabel_inspection")
                ->where("kode_barang", $data->kode_barang)
                ->orderByDesc("id_inspection")
                ->first();

            $data->last_inspection = $brokenApar; // simpan object inspection
            return $data;
        })
        ->filter(function($data) {
            return $data->last_inspection 
                && $data->last_inspection->status === 'rusak';
        })
        ->values(); // reset index biar rapi

        return response()->json([
            'message' => 'list data apar rusak',
            'data' => [
                "apar_broken" => $apar
            ]
        ], 200);
    }

    public function countApatInspection(Request $request)
    {
        $apar = DB::table("tabel_inspection")
            ->where("kode_customer", auth()->user()->kode_customer)
            ->pluck("kode_barang");

        $countInspection = DB::table("tabel_inspection")
            ->whereIn("kode_barang", $apar)
            ->select("kode_barang", DB::raw("COUNT(*) as total"))
            ->groupBy("kode_barang")
            ->get();
        $count = count($countInspection);

        return response()->json([
            'message' => 'Total apar yang telah diinspeksi',
            'data' => [
                "totalAparInspection" => $count
            ]
        ], 200);
    }

    public function presentaseInspectionDone (Request $request)
    {
      $data = DB::table("tabel_inspection")
        ->where("kode_customer", auth()->user()->kode_customer)
        ->where("status", '!=', 'rusak')
        ->count();

        $totalInspection = DB::table("tabel_inspection")
            ->where("kode_customer", auth()->user()->kode_customer)
            ->count();

        $percentageNotRusak = $totalInspection > 0 
            ? min(100, round(($data / $totalInspection) * 100, 2)) 
            : 0;

        $percentageRusak = $totalInspection > 0 
            ? min(100, round((($totalInspection - $data) / $totalInspection) * 100, 2)) 
            : 0;

        return response()->json([
            'message' => 'Persentase inspection',
            'data' => [
                'totalInspection'    => $totalInspection,
                'totalNotRusak'      => $data,
                'totalRusak'         => $totalInspection - $data,
                'percentageNotRusak' => $percentageNotRusak,
                'percentageRusak'    => $percentageRusak
            ]
        ], 200);

    }
    public function list_apar_pdf(Request $request)
    {
        $kop = DB::table("kop_surat")->where("type","inventory")->where("aktif","aktif")->where("kode_customer", auth()->user()->kode_customer)->first();
        $query = Product::where("kode_customer", auth()->user()->kode_customer) ->orderBy("id", "desc");
        $apar = $query->get()->map(function($item){
            $lokasi = DB::table("tabel_gedung")->where("id", $item->lokasi)->first();
            $lokasiPoint = DB::table("tabel_titik_penempatan")->where("id",$item->titik_penempatan_id)->first();
            $item->lokasi = $lokasi->nama_gedung ?? null;
            $item->titik_penempatan_id = $lokasiPoint->nama_titik ?? null;
            $kondisi_pressure = DB::table("tabel_detail_kondisi")->where("id", $item->pressure)->first();
            $kondisi_hose = DB::table("tabel_detail_kondisi")->where("id", $item->hose)->first();
            $kondisi_head_valve = DB::table("tabel_detail_kondisi")->where("id", $item->head_valve)->first();
            $kondisi_korosi = DB::table("tabel_detail_kondisi")->where("id", $item->korosi)->first();
            $kondisi_expired = DB::table("tabel_detail_kondisi")->where("id", $item->expired)->first();
            $apar_status = DB::table("tabel_inspection")
                ->where("kode_barang", $item->kode_barang)
                ->orderBy("id_inspection", "desc")
                ->first();
            $item->pressure = $kondisi_pressure->detail_kondisi ?? null;
            $item->hose = $kondisi_hose->detail_kondisi ?? null;
            $item->head_valve = $kondisi_head_valve->detail_kondisi ?? null;
            $item->korosi = $kondisi_korosi->detail_kondisi ?? null;
            $item->expired = $kondisi_expired->detail_kondisi ?? null;
            $item->status = $apar_status->status ?? null;
            return $item;
        });

        $pdf = Pdf::loadView('pdf.report_list_apar', [
        'apar' => $apar,
        'kop' => $kop
        ])->setPaper('A3', 'portrait');
        $customer_name = DB::table('tabel_master_customer')->where("kode_customer", auth()->user()->kode_customer)->first();

        // Simpan langsung ke storage/app/public/reports
        $fileName = 'Laporan Apar' . str_replace('/', '-', $customer_name->nama_customer) . '.pdf';
        $filePath = 'reports/' . $fileName;
        Storage::disk('public')->put($filePath, $pdf->output());

        // Buat URL publik untuk unduh
        $url = asset('storage/' . $filePath);

        return response()->json([
            'message' => 'Laporan berhasil dibuat',
            'download_url' =>  url('/api/inspection/download/' . $fileName),

        ]);
    }

}
