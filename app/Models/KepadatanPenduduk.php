<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KepadatanPenduduk extends Model
{
    protected $table = 'kepadatan_penduduk';

    public $timestamps = false;

    protected $fillable = [
        'wadmkc',
        'namobj',
        'kepadatan_penduduk',
        'jumlah_penduduk',
        'tahun',
        'gid',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'kepadatan_penduduk' => 'float',
        'jumlah_penduduk' => 'integer',
    ];

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'gid', 'gid');
    }

    public static function availableYears()
    {
        return static::select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();
    }

    public static function ClassifyKepadatan(float $kepadatanKm2) : array
    {
        $ha = $kepadatanKm2 / 100;

        $status = match (true) {
            $ha > 400   => 'Sangat Padat',
            $ha >= 201  => 'Tinggi',
            $ha >= 151  => 'Sedang',
            default     => 'Rendah'
        };

        return ['ha' => round($ha, 2), 'status' => $status];
    }
}