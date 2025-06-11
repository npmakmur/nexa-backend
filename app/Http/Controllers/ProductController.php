<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
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
                'tgl_produksi' => now(),
                'tgl_beli' => now(),
                'tgl_kadaluarsa' => now(),
                'garansi' => $request->garansi,
                'lokasi' => $request->lokasi,
                'kode_customer' => auth()->user()->kode_customer,
                'last_service' => now(),
                'last_refill' => now(),
                'last_inspection' => now(),
                'lokasi_id' => 1,
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
}
