<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RthKelurahan extends Model
{
    protected $table = 'data_rth_kelurahan_publik';

    protected $primaryKey = 'id';

    public $timestamps = false;

    // Field names mengikuti tabel baru:
    protected $fillable = [
        'gid',
        'wadmkc',
        'namobj',
        'luas_rth',
        'luas_kelurahan',
        'persentase_rth',
        'tahun',
    ];

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'gid', 'gid');
    }
}