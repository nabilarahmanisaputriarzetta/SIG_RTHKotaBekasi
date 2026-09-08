<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RthKelurahanSeeder extends Seeder
{
    /**
     * 12 kelurahan Kota Bekasi dengan data RTH fiktif namun realistis
     * berdasarkan proporsi wilayah sesungguhnya.
     * Sumber referensi luas wilayah: Kota Bekasi Dalam Angka 2023 (BPS).
     */
    public function run(): void
    {
        // [kelurahan, luas_wilayah_km2, penduduk_2023]
        $kelurahans = [
            ['Bekasi Timur',    13.49, 290_000],
            ['Bekasi Selatan',  14.96, 310_000],
            ['Bekasi Barat',    19.00, 370_000],
            ['Bekasi Utara',    19.60, 340_000],
            ['Medan Satria',    13.00, 215_000],
            ['Rawalumbu',       15.57, 280_000],
            ['Bantargebang',    17.07, 115_000],
            ['Mustika Jaya',    24.73, 295_000],
            ['Pondok Gede',     13.00, 320_000],
            ['Jati Sampurna',   14.37, 175_000],
            ['Jati Asih',       22.00, 330_000],
            ['Pondok Melati',   17.21, 210_000],
        ];

        // Persentase RTH per tahun per kelurahan (realistis, semua < 30 %)
        $pctMatrix = [
            // kelurahan        2023   2024   2025
            'Bekasi Timur'   => [8.2,   7.9,   7.6,   7.4],
            'Bekasi Selatan' => [11.5,  11.1,  10.8,  10.5],
            'Bekasi Barat'   => [9.8,   9.5,   9.2,   9.0],
            'Bekasi Utara'   => [10.3,  10.0,  9.7,   9.5],
            'Medan Satria'   => [12.1,  11.8,  11.4,  11.1],
            'Rawalumbu'      => [13.4,  13.0,  12.6,  12.3],
            'Bantargebang'   => [28.5,  27.9,  27.3,  26.8],  // paling hijau
            'Mustika Jaya'   => [22.3,  21.7,  21.1,  20.6],
            'Pondok Gede'    => [9.1,   8.8,   8.5,   8.3],
            'Jati Sampurna'  => [18.6,  18.1,  17.6,  17.2],
            'Jati Asih'      => [15.2,  14.8,  14.3,  14.0],
            'Pondok Melati'  => [20.7,  20.1,  19.6,  19.2],
        ];

        $years = [2023, 2024, 2025];
        $rows  = [];
        $now   = now();

        foreach ($kelurahans as [$nama, $luas, $penduduk]) {
            foreach ($years as $i => $tahun) {
                $pct     = $pctMatrix[$nama][$i];
                $luasRth = round($luas * $pct / 100, 4);
                $kepadat = round($penduduk / $luas, 2);

                $rows[] = [
                    'kelurahan'       => $nama,
                    'tahun'           => $tahun,
                    'luas_rth'        => $luasRth,
                    'luas_wilayah'    => $luas,
                    // pct_rth adalah generated column di PostgreSQL, tidak perlu diisi
                    'jumlah_penduduk' => $penduduk,
                    'kepadatan'       => $kepadat,
                    'catatan'         => null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }
        }

        // Insert tanpa pct_rth (generated column)
        DB::table('v_rth_kelurahan')->insertOrIgnore($rows);
    }
}