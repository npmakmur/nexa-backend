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
                // 'tgl_beli' => now(),
                'tgl_kadaluarsa' => $request->tanggal_kadaluarsa,
                'garansi' => $request->garansi,
                'lokasi' => $request->lokasi,
                'kode_customer' => auth()->user()->kode_customer,
                // 'last_service' => now(),
                // 'last_refill' => now(),
                // 'last_inspection' => now(),
            ]);

            $uniqueCode = 'PROD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT);

            // Generate PNG QR code
            $qrImage = Builder::create()
                ->writer(new PngWriter())
                ->data($uniqueCode)
                ->size(300)
                ->margin(10)
                ->build();

            $qrPath = 'qrcodes/' . $uniqueCode . '.png';
            Storage::disk('public')->put($qrPath, $qrImage->getString());

            $product->update([
                'barcode' => $uniqueCode,
                'kode_barang' => $uniqueCode,
            ]);

            $products[] = $product;

            $qrCodes[] = [
                'barcode' => $uniqueCode,
                'path' => $qrPath, // hanya path, nanti di-blade ditambahin public_path
            ];
        }

        $pdf = Pdf::loadView('pdf.qrcodes_pdf', ['qrCodes' => $qrCodes]);
        $pdfFileName = 'all_qr_codes_' . now()->format('Ymd_His') . '.pdf';
        $pdfPath = 'qrcodes_pdf/' . $pdfFileName;

        Storage::disk('public')->put($pdfPath, $pdf->output());
         $aktivitas = Aktivitas::create([
            'aktivitas_by' => auth()->user()->id,
            'aktivitas_name' => 'Menambahkan apar',
            'tanggal' => now(),
            'created_by' => auth()->user()->id,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Produk berhasil disimpan dan QR code PNG disertakan dalam PDF.',
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
    public function list_apar (Request $request)
    {
        $apar = Product::where("kode_customer", auth()->user()->kode_customer)->get();
          return response()->json([
            'message' => 'List apar berhasil didapatkan.',
            'List apar' => $apar,
        ]);
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'deskripsi' => 'nullable|string',
            'brand' => 'required|string|max:191',
            'type' => 'nullable|string|max:191',
            'media' => 'required|string|max:191',
            'kapasitas' => 'required|string|max:191',
            'tanggal_produksi'     => 'required|date',
            'tanggal_kadaluarsa'   => 'required|date|after_or_equal:tanggal_produksi',
        ]);

        $product = Product::findOrFail($id);

        $product->update([
            'deskripsi' => $request->deskripsi,
            'brand' => $request->brand,
            'type' => $request->type,
            'media' => $request->media,
            'kapasitas' => $request->kapasitas,
            'tgl_produksi' => $request->tanggal_produksi,
            'tgl_kadaluarsa' => $request->tanggal_kadaluarsa,
            'garansi' => $request->garansi,
            'lokasi' => $request->lokasi,
        ]);

        // Update QR jika barcode belum ada atau ada permintaan untuk perbarui QR
        if (!$product->barcode) {
            $uniqueCode = 'PROD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT);

            $qrImage = Builder::create()
                ->writer(new PngWriter())
                ->data($uniqueCode)
                ->size(300)
                ->margin(10)
                ->build();

            $qrPath = 'qrcodes/' . $uniqueCode . '.png';
            Storage::disk('public')->put($qrPath, $qrImage->getString());

            $product->update([
                'barcode' => $uniqueCode,
                'kode_barang' => $uniqueCode,
            ]);
        }

        // Catat aktivitas
        Aktivitas::create([
            'aktivitas_by' => auth()->user()->id,
            'aktivitas_name' => 'Mengupdate apar',
            'tanggal' => now(),
            'created_by' => auth()->user()->id,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'data' => $product,
        ]);
    }
}
