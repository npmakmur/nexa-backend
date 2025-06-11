<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Impor trait SoftDeletes

class Product extends Model
{
    use HasFactory, SoftDeletes; 
    protected $table = 'tabel_produk';
    protected $fillable = [
        'kode_barang',
        'barcode',
        'deskripsi',
        'brand',
        'type',
        'media',
        'kapasitas',
        'tgl_produksi',
        'tgl_beli',
        'tgl_kadaluarsa',
        'garansi',
        'lokasi',
        'kode_customer',
        'last_service',
        'last_refill',
        'last_inspection',
        'lokasi_id',
    ];

    protected $dates = [
        'tgl_produksi',
        'tgl_beli',
        'tgl_kadaluarsa',
        'garansi',
        'last_service',
        'last_refill',
        'last_inspection',
        'deleted_at', // Sangat penting untuk menyertakan 'deleted_at' di sini untuk SoftDeletes
    ];
}