<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TabelHeaderJadwal extends Model
{
    use SoftDeletes;

    // Nama tabel di database
    protected $table = 'tabel_header_jadwal';

    // Jika primary key bukan 'id' atau ingin custom, bisa diatur di sini
    protected $primaryKey = 'id';

    // Laravel otomatis mengatur created_at dan updated_at
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'no_jadwal',
        'no_po',
        'no_sj',
        'jenis_jadwal',
        'lokasi_id',
        'kode_barang',
        'barcode',
        'inspeksi_title',
        'inspeksi_confirm',
        'inspeksi_pic',
        'inspeksi_no_hp_pic',
        'jumlah_apar',
        'selesai',
        'status',
        'tgl_mulai',
        'tgl_selesai',
        'rentang_jam',
        'kode_customer',
        'kode_mitra',
        'kode_activity',
        'keterangan',
        'recurring_period',
        'level_lokasi',
        'kode_lokasi',
        'created_by',
        'execute_by',
    ];

    // Konversi otomatis ke Carbon (date format Laravel)
    protected $dates = [
        'tgl_mulai',
        'tgl_selesai',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
