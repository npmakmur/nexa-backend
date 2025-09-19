<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;


class PenawaranController extends Controller
{
    public function index (Request $request)
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
            $penawaran = 0;

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
            ->get()
            ->map(function($item) use (&$penawaran) {
                $product = Product::where("kode_barang", $item->kode_barang)->first();

                $penawaran_pressure = DB::table("penawaran")
                    ->where("checklist", "Pressure gauge")
                    ->where("kondisi", $item->detail_pressure)
                    ->first();
                if ($penawaran_pressure) {
                    $penawaran += $penawaran_pressure->harga;
                }

                $penawaran_selang = DB::table("penawaran")
                    ->where("checklist", "Selang")
                    ->where("kondisi", $item->detail_hose)
                    ->first();
                if ($penawaran_selang) {
                    $penawaran += $penawaran_selang->harga;
                }

                $penawaran_head_valve = DB::table("penawaran")
                    ->where("checklist", "Head valve")
                    ->where("kondisi", $item->detail_head_valve)
                    ->first();
                if ($penawaran_head_valve) {
                    $penawaran += $penawaran_head_valve->harga;
                }

                $penawaran_expired = DB::table("penawaran")
                    ->where("checklist", "Expired")
                    ->where("kondisi", $item->detail_expired)
                    ->where("media", $item->media)
                    ->first();
                if ($penawaran_expired) {
                    $penawaran += $product->kapasitas * $penawaran_expired->harga;
                }

                $penawaran_korosi = DB::table("penawaran")
                    ->where("checklist", "Korosi")
                    ->where("kondisi", $item->detail_korosi)
                    ->first();
                if ($penawaran_korosi) {
                    $penawaran += $product->kapasitas * $penawaran_korosi->harga;
                }
            }
        );
        $format_penawaran = "Rp " . number_format($penawaran, 0, ',', '.');
        return response()->json([
            'message' => 'detail penawaran',
            'penawaran' => $format_penawaran
        ], 201);
    }
    public function ReportPenawaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_jadwal' => 'required',
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
        $data_customer = DB::table("tabel_master_customer")->where("kode_customer", $data->kode_customer)->first();

        $penawaran = 0;
        $list_penawaran = [];

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
            ->get()
            ->map(function($item) use (&$penawaran, &$list_penawaran) {
                $product = Product::where("kode_barang", $item->kode_barang)->first();

                $parts = [
                    ['checklist' => 'Pressure gauge', 'kondisi' => $item->detail_pressure, 'qty' => 1, 'kapasitas' => false],
                    ['checklist' => 'Selang', 'kondisi' => $item->detail_hose, 'qty' => 1, 'kapasitas' => false],
                    ['checklist' => 'Head valve', 'kondisi' => $item->detail_head_valve, 'qty' => 1, 'kapasitas' => false],
                    ['checklist' => 'Expired', 'kondisi' => $item->detail_expired, 'qty' => $product->kapasitas, 'kapasitas' => true, 'media' => $item->media],
                    ['checklist' => 'Korosi', 'kondisi' => $item->detail_korosi, 'qty' => $product->kapasitas, 'kapasitas' => true],
                ];

                foreach ($parts as $part) {
                    $query = DB::table("penawaran")
                        ->where("checklist", $part['checklist'])
                        ->where("kondisi", $part['kondisi']);

                    if (isset($part['media'])) {
                        $query->where("media", $part['media']);
                    }

                    $penawaran_item = $query->first();

                    if ($penawaran_item) {
                        $subtotal = ($part['kapasitas'] ? $product->kapasitas : 1) * $penawaran_item->harga;
                        $penawaran += $subtotal;

                        $list_penawaran[] = [
                            'part' => $part['checklist'],
                            'kondisi' => $part['kondisi'],
                            'qty' => $part['kapasitas'] ? $product->kapasitas : 1,
                            'harga' => $penawaran_item->harga,
                            'subtotal' => $subtotal
                        ];
                    }
                }
            });

        // Buat PDF
        $pdf = Pdf::loadView('pdf.penawaran_apar', [
            'data' => $data,
            'list_penawaran' => $list_penawaran,
            'total' => $penawaran,
            'customer' => $data_customer
        ])->setPaper('a3', 'portrait');

        // Simpan file ke storage/app/public/reports
        $fileName = 'Penawaran_APAR_' . str_replace('/', '-', $data->no_jadwal) . '.pdf';
        $filePath = 'reports/' . $fileName;
        Storage::disk('public')->put($filePath, $pdf->output());
        $fileName = base64_encode($fileName);
        //  return view('pdf.penawaran_apar', [
        //     'data' => $data,
        //     'list_penawaran' => $list_penawaran,
        //     'total' => $penawaran,
        //     'customer' => $data_customer
        // ]);
        // Return link download
        return response()->json([
            'status' => true,
            'message' => 'Laporan penawaran berhasil dibuat',
            'download_url' =>  url('/api/penawaran/download/' . $fileName)
        ]);
    }
    public function download($file)
    {
        $file = base64_decode($file);
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
}
