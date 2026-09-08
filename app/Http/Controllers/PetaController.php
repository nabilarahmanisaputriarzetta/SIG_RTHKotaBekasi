<?php

namespace App\Http\Controllers;

use App\Models\OverlaySpasial;

use Illuminate\Http\Request;

class PetaController extends Controller
{
    public function index()

    {
        $years = [2023, 2024, 2025];
        // default tahun untuk view peta
        $year = (int) request()->get('year', 2025);

        return view('peta', compact('years', 'year'));
    }


    // ── API: RTH per kelurahan ────────────────────────────────────────────────
    public function apiRth(int $tahun)
    {
        try {
            // Sumber utama sekarang: vw_overlay_spasial
            $raw = OverlaySpasial::query()
                ->select([
                    'gid',
                    'namobj',
                    'wadmkc',
                    'tahun',
                    'luas_rth',
                    'luas_kelurahan',
                    'persentase_rth',
                    'kepadatan_penduduk',
                    'geom',
                ])
                ->where('tahun', $tahun)
                ->get()
                ->map(function ($r) {
                    $namaKel = strtoupper(trim($r->namobj));
                    $kec      = strtoupper(trim($r->wadmkc));
                    $luasRth = (float) ($r->luas_rth ?? 0);
                    $luasKelurahan = (float) ($r->luas_kelurahan ?? 0);

                    // gunakan presentase RTH yang dihitung dari luas_rth dan luas_kelurahan, bukan dari kolom persentase_rth di database (karena bisa berbeda-beda tergantung sumber data)
                    $persentaseRth = $luasKelurahan > 0
                        ? ($luasRth / $luasKelurahan) * 100
                        : 0;

                    // kepadatan penduduk -> penduduk: diasumsikan kepadatan = jiwa/km² dan luas_kelurahan = km²
                    $kepadatanPenduduk = (float) ($r->kepadatan_penduduk ?? 0);
                    $jumlahPenduduk = $kepadatanPenduduk > 0 && $luasKelurahan > 0
                        ? round($kepadatanPenduduk * $luasKelurahan)
                        : 0;

                    // ── Status per kelurahan: Permen PU No. 05/PRT/M/2008 ──
                    // RTH skala kelurahan MEMENUHI jika RTH per kapita ≥ 0,30 m²/jiwa
                    // DAN luas RTH ≥ 9.000 m². Threshold 20% (persentase_rth) HANYA
                    // dipakai untuk ringkasan agregat kota (lihat $pctKota di bawah),
                    // BUKAN untuk menilai kelurahan satu-satu.
                    $luasRthM2 = $luasRth * 1_000_000;
                    $rthPerKapita = $jumlahPenduduk > 0
                        ? $luasRthM2 / $jumlahPenduduk
                        : 0;
                    $memenuhiStandarKelurahan =
                        $jumlahPenduduk > 0
                        && $rthPerKapita >= 0.30
                        && $luasRthM2 >= 9000;

                    $statusPersentase = $memenuhiStandarKelurahan ? 'Memenuhi' : 'Belum Memenuhi';
                    $status = $statusPersentase;

                    return [
                        'gid' => $r->gid,
                        'kelurahan' => $namaKel,
                        'kecamatan' => $kec,
                        'luas_rth_raw' => $luasRth,
                        'luas_kelurahan_raw' => $luasKelurahan,
                        'penduduk' => $jumlahPenduduk,
                        // Diekspos dalam jiwa/ha (÷100 dari kepadatan_penduduk yang
                        // tersimpan jiwa/km²). Perhitungan $jumlahPenduduk di atas
                        // tetap pakai kepadatan asli (km²) karena luas_kelurahan km².
                        'kepadatan' => $kepadatanPenduduk / 100,
                        'status' => $status,
                        'persentase_rth' => $persentaseRth,
                        'rth_per_kapita' => $rthPerKapita,
                        'status_persentase' => $statusPersentase,
                    ];
                });

            $memenuhi = $raw->filter(
                fn ($d) => $d['status'] === 'Memenuhi'
            )->count();

            $dibawah = $raw->filter(
                fn ($d) => $d['status'] === 'Belum Memenuhi'
            )->count();

            // ── Ringkasan kota dihitung dari nilai MENTAH dulu, baru dibulatkan
            //    sekali di akhir. Ini yang bikin pctKota sama persis dengan
            //    persen_rth di halaman Data (dua-duanya jumlah-dulu-baru-bulatkan). ──
            $totalRthRaw     = $raw->sum('luas_rth_raw');
            $totalWilayahRaw = $raw->sum('luas_kelurahan_raw');

            $totalRth     = round($totalRthRaw, 2);
            $totalWilayah = round($totalWilayahRaw, 2);

            $pctKota = $totalWilayahRaw > 0
                ? round(($totalRthRaw / $totalWilayahRaw) * 100, 2)
                : 0;

            $avgKel = round($raw->avg('persentase_rth'), 2);

            // ── Baru sekarang dibulatkan untuk tampilan per-kelurahan (sidebar/popup) ──
            $rows = $raw->map(function ($d) {
                return [
                    'gid' => $d['gid'],
                    'kelurahan' => $d['kelurahan'],
                    'kecamatan' => $d['kecamatan'],
                    'luas_rth' => round($d['luas_rth_raw'], 4),
                    'luas_kelurahan' => round($d['luas_kelurahan_raw'], 2),
                    'luas_wilayah_kel' => round($d['luas_kelurahan_raw'], 2),
                    'penduduk' => $d['penduduk'],
                    'kepadatan' => round($d['kepadatan'], 2),
                    'status' => $d['status'],
                    'persentase_rth' => round($d['persentase_rth'], 4),
                    'rth_per_kapita' => round($d['rth_per_kapita'], 4),
                    'status_persentase' => $d['status_persentase'],
                ];
            });

            return response()->json([
                'tahun' => $tahun,
                'data' => $rows->values(),
                'summary' => [
                    'memenuhi' => $memenuhi,
                    'dibawah' => $dibawah,
                    'totalRth' => $totalRth,
                    'totalWilayah' => $totalWilayah,
                    'pctKota' => $pctKota,
                    'avgKel' => $avgKel,
                    'statusKota' => $pctKota >= 20
                        ? 'Memenuhi'
                        : 'Belum Memenuhi',
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'api_rth_failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ── API: Kepadatan penduduk ───────────────────────────────────────────────
    public function apiKepadatan(int $tahun)
    {
        $rows = OverlaySpasial::where('tahun', $tahun)
            ->get()
            ->map(function ($r) {

                // vw_overlay_spasial sudah mengembalikan luas_kelurahan dalam km²
                // langsung, jadi tidak perlu dibagi 100 lagi (lihat apiRth).
                $luasWilayah   = (float) ($r->luas_kelurahan ?? 0);
                $kepadatanKm2  = (float) ($r->kepadatan_penduduk ?? 0); // jiwa/km² (satuan asli kolom)
                $jumlahPenduduk = $kepadatanKm2 > 0 && $luasWilayah > 0
                    ? round($kepadatanKm2 * $luasWilayah)
                    : 0;

                // Diekspos dalam jiwa/ha (÷100) supaya sejalan dengan tabel SNI
                // 03-1733-2004 yang memang berbasis per hektare.
                $kepadatanHa = $kepadatanKm2 / 100;

                return [
                    'gid' => $r->gid,
                    'kelurahan' => strtoupper(trim($r->namobj)),
                    'kepadatan' => round($kepadatanHa, 2),
                    'luas_wilayah' => round($luasWilayah, 2),
                    'jumlah_penduduk' => $jumlahPenduduk,
                    // Klasifikasi SNI 03-1733-2004: Rendah <150, Sedang 151-200,
                    // Tinggi 201-400, Sangat Padat >400 jiwa/ha.
                    'status' => $kepadatanHa > 400
                        ? 'Sangat Padat'
                        : ($kepadatanHa >= 201
                            ? 'Tinggi'
                            : ($kepadatanHa >= 151 ? 'Sedang' : 'Rendah'))
                ];
            });

        return response()->json([
            'tahun' => $tahun,
            'data'  => $rows->values()
        ]);
    }

    // ── API: Compare dua tahun ────────────────────────────────────────────────
    public function apiOverlay(int $tahun)
    {
        $rows = \Illuminate\Support\Facades\DB::select(
            "
            SELECT
                gid,
                namobj AS kelurahan,
                wadmkc AS kecamatan,
                tahun,
                kepadatan_penduduk,
                luas_rth,
                luas_kelurahan,
                persentase_rth,
                ST_AsGeoJSON(geom) as geom
            FROM vw_overlay_spasial
            WHERE tahun = ?
            ",
            [$tahun]
        );

        $features = [];

        foreach ($rows as $row) {
            $geomJson = $row->geom;
            $geometry = is_string($geomJson) ? json_decode($geomJson) : $geomJson;

            if (!is_object($geometry) || empty($geometry->type) || !property_exists($geometry, 'coordinates')) {
                \Illuminate\Support\Facades\Log::warning('PetaController: geojson geometry invalid/empty', [
                    'tahun' => $tahun,
                    'kelurahan' => $row->kelurahan,
                    'kecamatan' => $row->kecamatan,
                ]);
                continue;
            }

            $geometry->coordinates = (array) ($geometry->coordinates ?? []);

            // vw_overlay_spasial sudah km² langsung, tidak perlu ÷100 lagi.
            $luasRthRaw       = (float) $row->luas_rth;
            $luasKelurahanRaw = (float) $row->luas_kelurahan;
            $kepadatanRaw     = (float) $row->kepadatan_penduduk;
            $jumlahPenduduk   = $kepadatanRaw > 0 && $luasKelurahanRaw > 0
                ? round($kepadatanRaw * $luasKelurahanRaw)
                : 0;

            // Status per kelurahan: Permen PU No. 05/PRT/M/2008 (0,30 m²/jiwa &
            // ≥9.000 m² RTH). BUKAN threshold 20% — itu hanya untuk agregat kota.
            $luasRthM2    = $luasRthRaw * 1_000_000;
            $rthPerKapita = $jumlahPenduduk > 0 ? round($luasRthM2 / $jumlahPenduduk, 4) : 0;
            $memenuhiStandarKelurahan =
                $jumlahPenduduk > 0 && $rthPerKapita >= 0.30 && $luasRthM2 >= 9000;

            $features[] = [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'gid'            => $row->gid,
                    'kelurahan'      => $row->kelurahan,
                    'kecamatan'      => $row->kecamatan,
                    // jiwa/ha (÷100) — konsisten dengan apiRth/apiKepadatan.
                    'kepadatan'      => round($kepadatanRaw / 100, 2),
                    'luas_rth'       => round($luasRthRaw, 3),
                    'luas_kelurahan' => round($luasKelurahanRaw, 3),
                    'persentase_rth' => $row->persentase_rth,
                    'jumlah_penduduk'=> $jumlahPenduduk,
                    'rth_per_kapita' => $rthPerKapita,
                    'memenuhi_standar_kelurahan' => $memenuhiStandarKelurahan,
                    'tahun'          => $row->tahun,
                ],
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    // ── API: Compare dua tahun ────────────────────────────────────────────────
    public function apiCompare(int $tahun1, int $tahun2)

    {
        $data1 = OverlaySpasial::query()
            ->select([
                'gid',
                'namobj',
                'tahun',
                'luas_rth',
                'luas_kelurahan',
                'kepadatan_penduduk'
            ])
            ->where('tahun', $tahun1)
            ->get()
            ->keyBy('gid');

        $data2 = OverlaySpasial::query()
            ->select([
                'gid',
                'namobj',
                'tahun',
                'luas_rth',
                'luas_kelurahan',
                'persentase_rth',
                'kepadatan_penduduk'
            ])
            ->where('tahun', $tahun2)
            ->get()
            ->keyBy('gid');

        $allGid = $data1->keys()->merge($data2->keys())->unique();

        foreach ($allGid as $gid) {

            $r1 = $data1->get($gid);
            $r2 = $data2->get($gid);

            $nama = $r2->namobj ?? $r1->namobj ?? '-';

            // vw_overlay_spasial sudah km² langsung, tidak perlu ÷100 lagi.
            $luasRth1 = $r1 ? (float)$r1->luas_rth : 0;
            $luasRth2 = $r2 ? (float)$r2->luas_rth : 0;

            // Ambil luas wilayah tahun yang sama
            $luasWil1 = $r1 ? (float)$r1->luas_kelurahan : 0;
            $luasWil2 = $r2 ? (float)$r2->luas_kelurahan : 0;

            $pct1 = $luasWil1 > 0 ? ($luasRth1 / $luasWil1) * 100 : 0;
            $pct2 = $luasWil2 > 0 ? ($luasRth2 / $luasWil2) * 100 : 0;
            $diff = round($pct2 - $pct1, 4);

            // ── Status Memenuhi/Belum per tahun (Permen PU No.05/PRT/M/2008),
            //    dipakai untuk klasifikasi transisi antar dua tahun. Rumus sama
            //    persis dengan apiRth/apiOverlay: per-kapita ≥0,30 m²/jiwa DAN
            //    luas ≥9.000 m². ──
            $kepadatan1 = $r1 ? (float) ($r1->kepadatan_penduduk ?? 0) : 0;
            $kepadatan2 = $r2 ? (float) ($r2->kepadatan_penduduk ?? 0) : 0;

            $penduduk1 = $kepadatan1 > 0 && $luasWil1 > 0 ? round($kepadatan1 * $luasWil1) : 0;
            $penduduk2 = $kepadatan2 > 0 && $luasWil2 > 0 ? round($kepadatan2 * $luasWil2) : 0;

            $luasRthM2_1 = $luasRth1 * 1_000_000;
            $luasRthM2_2 = $luasRth2 * 1_000_000;

            $rthPerKapita1 = $penduduk1 > 0 ? $luasRthM2_1 / $penduduk1 : 0;
            $rthPerKapita2 = $penduduk2 > 0 ? $luasRthM2_2 / $penduduk2 : 0;

            $memenuhi1 = $penduduk1 > 0 && $rthPerKapita1 >= 0.30 && $luasRthM2_1 >= 9000;
            $memenuhi2 = $penduduk2 > 0 && $rthPerKapita2 >= 0.30 && $luasRthM2_2 >= 9000;

            if ($memenuhi1 && $memenuhi2) {
                $transisi = 'konsisten_memenuhi';
            } elseif (!$memenuhi1 && !$memenuhi2) {
                $transisi = 'konsisten_belum';
            } elseif (!$memenuhi1 && $memenuhi2) {
                $transisi = 'membaik';
            } else {
                $transisi = 'memburuk';
            }

            $result[] = [
                'gid'            => $gid,
                'kelurahan'      => strtoupper(trim($nama)),
                'pct_tahun1'     => round($pct1, 4),
                'pct_tahun2'     => round($pct2, 4),
                'luas_tahun1'    => $r1 ? round((float)$r1->luas_rth, 4) : 0,
                'luas_tahun2'    => $r2 ? round((float)$r2->luas_rth, 4) : 0,
                'diff_pct'       => $diff,
                'trend'          => $diff > 0 ? 'naik' : ($diff < 0 ? 'turun' : 'tetap'),
                // ── Status transisi Permen PU (bukan overlay spasial — lihat
                //    diskusi metodologi: ini komparasi status/atribut antar
                //    dua tahun untuk unit spasial yang sama, bukan operasi
                //    geometri ST_Intersection/ST_Difference). ──
                'status_tahun1'  => $memenuhi1 ? 'Memenuhi' : 'Belum Memenuhi',
                'status_tahun2'  => $memenuhi2 ? 'Memenuhi' : 'Belum Memenuhi',
                'transisi'       => $transisi,
            ];
        }

        usort($result, fn($a, $b) => $a['diff_pct'] <=> $b['diff_pct']);

        $col     = collect($result);
        // vw_overlay_spasial sudah km² langsung, tidak perlu ÷100 lagi.
        $sum1Rth = $data1->sum(fn($r)=>(float)$r->luas_rth);
        $sum2Rth = $data2->sum(fn($r)=>(float)$r->luas_rth);
        $selisih = round($sum2Rth - $sum1Rth, 4);

        return response()->json([
            'tahun'        => $tahun1,
            'data'         => $result,
            'turun'        => $col->where('trend', 'turun')->count(),
            'tetap'        => $col->where('trend', 'tetap')->count(),
            'naik'         => $col->where('trend', 'naik')->count(),
            'total_tahun1' => round($sum1Rth, 4),
            'total_tahun2' => round($sum2Rth, 4),
            'selisih'      => $selisih,
        ]);
    }
}