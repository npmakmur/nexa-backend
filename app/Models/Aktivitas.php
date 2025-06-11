<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aktivitas extends Model
{
    use SoftDeletes;

    protected $table = 'tabel_aktivitas';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'aktivitas_by',
        'aktivitas_name',
        'tanggal',
        'created_by',
        'created_at',
        'updated_by',
        'update_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $dates = ['deleted_at'];
}
