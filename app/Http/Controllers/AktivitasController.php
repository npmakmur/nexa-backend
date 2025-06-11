<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AktivitasController extends Controller
{
    public function show (Request $request)
    {
        $kodeUserLogin = auth()->user()->kode_customer;

        $aktivitas = DB::table('tabel_aktivitas')
            ->join('users', 'tabel_aktivitas.aktivitas_by', '=', 'users.id')
            ->where('users.kode_customer', $kodeUserLogin)
            ->select('tabel_aktivitas.*') // bisa tambah kolom lain jika perlu
            ->get();

        return response()->json([
            'message' => 'Aktivitas oleh user login',
            'data' => $aktivitas,
        ], 200);
    }
}
