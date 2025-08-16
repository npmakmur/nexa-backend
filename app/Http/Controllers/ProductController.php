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
        $query = Product::where("kode_customer", auth()->user()->kode_customer);

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

        $apar = $query->get();

        return response()->json([
            'message' => 'List APAR berhasil didapatkan.',
            'list_apar' => $apar,
        ], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:tabel_produk,id',
            'deskripsi' => 'nullable|string',
            'brand' => 'required|string|max:191',
            'type' => 'nullable|string|max:191',
            'media' => 'required|string|max:191',
            'kapasitas' => 'required|string|max:191',
            'tanggal_produksi' => 'required|date',
            'tanggal_kadaluarsa' => 'required|date|after_or_equal:tanggal_produksi',
            'garansi' => 'nullable|string|max:191',
            'lokasi' => 'required|exists:tabel_gedung,id',
            'titik_penempatan_id' => 'required'
        ]);

       $product = Product::find($request->id);

        if ($product) {
            $product->deskripsi = $request->deskripsi;
            $product->brand = $request->brand;
            $product->type = $request->type;
            $product->media = $request->media;
            $product->kapasitas = $request->kapasitas;
            $product->tgl_produksi = $request->tanggal_produksi;
            $product->tgl_kadaluarsa = $request->tanggal_kadaluarsa;
            $product->garansi = $request->garansi;
            $product->lokasi = $request->lokasi;
            $product->titik_penempatan_id = $request->titik_penempatan_id;
            $product->updated_at = now();
            $product->save();
        }

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada perubahan atau produk tidak ditemukan.',
            ], 400);
        }

        // Ambil data produk terbaru setelah update
        $product = DB::table('tabel_produk')->where('id', $request->id)->first();

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
            'data' => $product,
            'updated_at' => $now->toDateTimeString(),
        ], 200);
    }
    public function detai_apar(Request $request)
    {
        $request->validate([
            'kode_barang' => '',
        ]);
        $product = Product::select(
            'tabel_produk.*', 
            'pressure_kondisi.detail_kondisi as detail_pressure',
            'hose_kondisi.detail_kondisi as detail_hose',
            'head_valve_kondisi.detail_kondisi as detail_head_valve',
            'korosi_kondisi.detail_kondisi as detail_korosi',
            'expired_kondisi.detail_kondisi as detail_expired',
        )
        ->leftJoin('tabel_detail_kondisi as pressure_kondisi', 'tabel_produk.pressure', '=', 'pressure_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as hose_kondisi', 'tabel_produk.hose', '=', 'hose_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as head_valve_kondisi', 'tabel_produk.head_valve', '=', 'head_valve_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as korosi_kondisi', 'tabel_produk.korosi', '=', 'korosi_kondisi.id')
        ->leftJoin('tabel_detail_kondisi as expired_kondisi', 'tabel_produk.expired', '=', 'expired_kondisi.id')

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
                'data' => '0%', 200
            ]);
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
}
