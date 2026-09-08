<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = (int) $request->get('year', 2025);

        // ── Daftar tahun yang tersedia di data (dipakai untuk dropdown tahun
        //    DAN untuk menentukan tahun pembanding analisis perubahan RTH). ──
        $availableYears = DB::table('vw_overlay_spasial')
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun')
            ->map(fn ($y) => (int) $y)
            ->values();

        // ── Tahun pembanding: tahun terbesar yang lebih kecil dari tahun
        //    terpilih (mis. terpilih 2025 → pembanding 2024). Kalau tahun
        //    terpilih adalah tahun paling awal di data (tidak ada tahun
        //    sebelumnya), $tahunPembanding bernilai null dan seluruh field
        //    "perubahan" di bawah otomatis kosong (tidak ada yang dibagi). ──
        $tahunPembanding = $availableYears->filter(fn ($y) => $y < $selectedYear)->max();

        $prevByGid = collect();
        if ($tahunPembanding) {
            $prevByGid = DB::table('vw_overlay_spasial')
                ->where('tahun', $tahunPembanding)
                ->select(['gid', 'luas_rth', 'luas_kelurahan'])
                ->get()
                ->keyBy('gid');
        }

        // ── Ambil data & standarisasi satuan / populasi ───
        $rows = DB::table('vw_overlay_spasial')
            ->where('tahun', $selectedYear)
            ->get()
            ->map(function ($r) {
                $r->luas_rth_km2       = (float) ($r->luas_rth ?? 0);
                $r->luas_kelurahan_km2 = (float) ($r->luas_kelurahan ?? 0);
                $r->kepadatan_penduduk = (float) ($r->kepadatan_penduduk ?? 0);

                // Kolom persentase_rth di data_rth_kelurahan_publik (sumber
                // vw_overlay_spasial) skalanya tidak konsisten — sebagian baris
                // tersimpan sebagai persen (0-100), sebagian sebagai rasio
                // mentah (0-1) yang lupa dikali 100. Jadi dihitung ulang di
                // sini dari luas_rth/luas_kelurahan, sama seperti yang sudah
                // dilakukan PetaController::apiRth, supaya tidak tergantung
                // kualitas data kolom tersebut.
                $r->persentase_rth = $r->luas_kelurahan_km2 > 0
                    ? ($r->luas_rth_km2 / $r->luas_kelurahan_km2) * 100
                    : 0;

                $r->jumlah_penduduk = (int) ($r->jumlah_penduduk ?? 0);

                // ── Standar RTH skala kelurahan: Permen PU No. 05/PRT/M/2008 ──
                // RTH per kapita ≥ 0,30 m²/jiwa DAN luas RTH ≥ 9.000 m².
                // Ini yang menentukan status "Memenuhi/Belum Memenuhi" PER KELURAHAN.
                // Threshold 20% (persentase_rth) HANYA berlaku untuk agregat kota
                // (lihat $persenRth / stats['persen_rth']), TIDAK dipakai untuk
                // menilai kelurahan satu-satu.
                $luasRthM2 = $r->luas_rth_km2 * 1_000_000;
                $r->rth_per_kapita = $r->jumlah_penduduk > 0
                    ? round($luasRthM2 / $r->jumlah_penduduk, 4)
                    : 0;
                $r->memenuhi_standar_kelurahan =
                    $r->jumlah_penduduk > 0
                    && $r->rth_per_kapita >= 0.30
                    && $luasRthM2 >= 9000;

                return $r;
            });

        // ── Perubahan luas RTH per kelurahan vs tahun pembanding ──
        // Inilah inti dari "Analisis Perubahan Luas RTH" pada judul: tiap
        // kelurahan dibandingkan luas & persentase RTH-nya dengan tahun
        // sebelumnya yang tersedia. diff > 0 → RTH bertambah ("naik"),
        // diff < 0 → RTH berkurang ("turun"), 0 → "tetap".
        $rows = $rows->map(function ($r) use ($prevByGid, $tahunPembanding) {
            $prev = $tahunPembanding ? $prevByGid->get($r->gid) : null;

            if ($prev) {
                $prevLuasRthKm2 = (float) ($prev->luas_rth ?? 0);
                $prevLuasKelKm2 = (float) ($prev->luas_kelurahan ?? 0);
                $prevPersenRth  = $prevLuasKelKm2 > 0
                    ? ($prevLuasRthKm2 / $prevLuasKelKm2) * 100
                    : 0;

                $r->luas_rth_km2_prev   = $prevLuasRthKm2;
                $r->persentase_rth_prev = round($prevPersenRth, 4);
                $r->diff_luas_rth_km2   = round($r->luas_rth_km2 - $prevLuasRthKm2, 4);
                $r->diff_persen_rth     = round($r->persentase_rth - $prevPersenRth, 4);
                $r->trend_rth = $r->diff_persen_rth > 0.0001
                    ? 'naik'
                    : ($r->diff_persen_rth < -0.0001 ? 'turun' : 'tetap');
            } else {
                $r->luas_rth_km2_prev   = null;
                $r->persentase_rth_prev = null;
                $r->diff_luas_rth_km2   = null;
                $r->diff_persen_rth     = null;
                $r->trend_rth           = null;
            }

            return $r;
        });

        // =========================
        // RINGKASAN
        // =========================

        $totalKelurahan = $rows->count();

        $totalPenduduk = $rows->sum('jumlah_penduduk');

        $totalRth  = $rows->sum('luas_rth_km2');
        $totalLuas = $rows->sum('luas_kelurahan_km2');

        $persenRth = $totalLuas > 0
            ? round(($totalRth / $totalLuas) * 100, 2)
            : 0;

        // Status per kelurahan: Permen PU 05/2008 (0,30 m²/jiwa & ≥9.000 m² RTH),
        // BUKAN threshold 20% — itu hanya untuk persenRth (agregat kota) di atas.
        $memenuhi = $rows->where('memenuhi_standar_kelurahan', true)->count();

        $belumMemenuhi = $totalKelurahan - $memenuhi;

        // RTH per kapita (m²/jiwa) = luas RTH (m²) / jumlah penduduk
        // 1 km² = 1.000.000 m² — totalRth di sini sudah dalam km².
        $rthPerKapita = $totalPenduduk > 0
            ? round(($totalRth * 1_000_000) / $totalPenduduk, 2)
            : 0;

        // Defisit per kelurahan dihitung terhadap standar Permen PU 05/2008:
        // target luas RTH kelurahan = MAX(9.000 m², 0,30 m²/jiwa × penduduk).
        // Dijumlahkan dalam km² supaya sebanding dengan totalRth / totalWilayah
        // di atas. (Bukan target 20% dari luas kelurahan — itu hanya berlaku
        // untuk agregat kota.)
        $totalDefisit = round($rows->sum(function ($r) {
            $targetM2  = max(9000, 0.30 * $r->jumlah_penduduk);
            $luasRthM2 = $r->luas_rth_km2 * 1_000_000;
            $defisitM2 = max(0, $targetM2 - $luasRthM2);
            return $defisitM2 / 1_000_000; // → km²
        }), 4);

        // Kepadatan kota = total penduduk ÷ total luas wilayah (rata-rata tertimbang),
        // BUKAN rata-rata sederhana dari kepadatan per kelurahan — supaya kelurahan
        // kecil-padat tidak mendominasi angka, dan angkanya konsisten dengan
        // "Kepadatan Rata-rata" yang tampil di halaman Peta.
        $kepadatanKota = $totalLuas > 0
            ? round($totalPenduduk / $totalLuas)
            : 0;

        // ── Ringkasan perubahan RTH kota dibanding tahun pembanding ──
        // Ini yang menjawab "Analisis Perubahan Luas RTH" di judul secara
        // agregat kota: total km² RTH kota bertambah/berkurang berapa, dan
        // berapa kelurahan yang RTH-nya naik/turun/tetap dibanding tahun lalu.
        $kelurahanNaik  = $rows->where('trend_rth', 'naik')->count();
        $kelurahanTurun = $rows->where('trend_rth', 'turun')->count();
        $kelurahanTetap = $rows->where('trend_rth', 'tetap')->count();

        $totalRthPrev = $tahunPembanding
            ? round($rows->sum('luas_rth_km2_prev'), 4)
            : null;

        $diffLuasRthKota = $tahunPembanding
            ? round($totalRth - $totalRthPrev, 4)
            : null;

        $persenRthPrevKota = ($tahunPembanding && $totalLuas > 0)
            ? round(($totalRthPrev / $totalLuas) * 100, 2)
            : null;

        $diffPersenRthKota = ($tahunPembanding && $persenRthPrevKota !== null)
            ? round($persenRth - $persenRthPrevKota, 2)
            : null;

        $stats = [

            'total_kelurahan'=>$totalKelurahan,

            'total_penduduk'=>round($totalPenduduk),

            'kepadatan_kota'=>$kepadatanKota,

            'persen_rth'=>$persenRth,

            'rth_per_kapita'=>$rthPerKapita,

            'kelurahan_kritis'=>$belumMemenuhi,

            'total_defisit'=>$totalDefisit,

            'kelurahan_memenuhi'=>$memenuhi,

            'capaian_target'=>round(($persenRth/20)*100,2),

            // ── Analisis perubahan luas RTH (vs tahun pembanding) ──
            'tahun_pembanding'=>$tahunPembanding,

            'diff_persen_rth_kota'=>$diffPersenRthKota,

            'diff_luas_rth_kota_km2'=>$diffLuasRthKota,

            'kelurahan_naik'=>$kelurahanNaik,

            'kelurahan_turun'=>$kelurahanTurun,

            'kelurahan_tetap'=>$kelurahanTetap,

        ];

        // =========================
        // PRIORITY DATA

        // Skor prioritas: 50% defisit RTH terhadap standar Permen PU 05/2008
        // (0,30 m²/jiwa, min. 9.000 m² — BUKAN 20%, karena itu hanya untuk
        // agregat kota) + 30% kepadatan (dinormalisasi) + 20% defisit RTH
        // per kapita terhadap target minimum 9 m²/jiwa (acuan WHO untuk
        // ruang hijau perkotaan per kapita).
        $maxKepadatan = $rows->max('kepadatan_penduduk') ?: 1;

        $priorityScores = [];

        $priorityData = $rows
            ->map(function ($r) use ($maxKepadatan, &$priorityScores) {
                $targetM2Kel  = max(9000, 0.30 * $r->jumlah_penduduk);
                $luasRthM2Kel = $r->luas_rth_km2 * 1_000_000;
                $defisitRth   = max(0, $targetM2Kel - $luasRthM2Kel);
                $skorDefisit  = $targetM2Kel > 0 ? min(100, ($defisitRth / $targetM2Kel) * 100) : 0;

                $skorPenduduk = ($r->kepadatan_penduduk / $maxKepadatan) * 100;

                // RTH per kapita per-kelurahan dalam m²/jiwa.
                $rthKapita = $r->jumlah_penduduk > 0
                    ? ($r->luas_rth_km2 * 1_000_000) / $r->jumlah_penduduk
                    : 0;
                // Makin rendah RTH per kapita → makin tinggi prioritas
                $defisitKapita = max(0, 9 - $rthKapita);
                $skorKapita    = ($defisitKapita / 9) * 100;

                $skorAkhir = round(
                    ($skorDefisit * 0.50) + ($skorPenduduk * 0.30) + ($skorKapita * 0.20),
                    2
                );

                $priorityScores[$r->gid] = $skorAkhir;

                return [
                    'gid'  => $r->gid,
                    'nama' => $r->namobj,
                    'skor' => $skorAkhir,
                ];
            })
            ->sortByDesc('skor')
            ->take(15)
            ->values();

        // =========================
        // PIE DATA
        $pieData = [
            ['label' => 'Memenuhi',       'count' => $memenuhi,       'color' => '#2e7d32'],
            ['label' => 'Belum Memenuhi', 'count' => $belumMemenuhi,  'color' => '#d32f2f'],
        ];

        // =========================
        // DISTRIBUSI
        $kategoriDist = [
            ['key' => 'AMAN',   'label' => 'Memenuhi',       'count' => $memenuhi,      'color' => '#2e7d32'],
            ['key' => 'KRITIS', 'label' => 'Belum Memenuhi', 'count' => $belumMemenuhi, 'color' => '#d32f2f'],
        ];

        // =========================
        // TOP RTH TERENDAH
        $rthBarData = $rows
            ->sortBy('persentase_rth')
            ->take(15)
            ->map(function ($r) {
                return [
                    'gid'  => $r->gid,
                    'nama'      => $r->namobj,
                    'persen_rth'=> round($r->persentase_rth, 2),
                ];
            })
            ->values();

        // =========================
        // TOP KEPADATAN
        $popData = $rows
            ->sortByDesc('jumlah_penduduk')
            ->take(15)
            ->map(function ($r) {
                return [
                    'gid'  => $r->gid,
                    'nama'      => $r->namobj,
                    'jumlah_penduduk' => round($r->jumlah_penduduk),
                ];
            })
            ->values();

        // =========================
        // TOP PERUBAHAN RTH (analisis perubahan luas RTH per kelurahan)
        // Diurutkan dari yang paling menurun ke yang paling naik, supaya
        // urutan awal langsung menunjukkan kelurahan paling kritis mengalami
        // penyusutan RTH dibanding tahun pembanding. Kosong kalau tidak ada
        // tahun pembanding (mis. tahun terpilih = tahun paling awal data).
        $rthChangeData = $tahunPembanding
            ? $rows
                ->filter(fn ($r) => $r->diff_persen_rth !== null)
                ->sortBy('diff_persen_rth')
                ->take(15)
                ->map(function ($r) {
                    return [
                        'gid'             => $r->gid,
                        'nama'            => $r->namobj,
                        'diff_persen_rth' => $r->diff_persen_rth,
                        'trend_rth'       => $r->trend_rth,
                    ];
                })
                ->values()
            : collect();

        // =========================
        // TREND
        // Catatan: rth% di trend adalah rasio totalRth/totalLuas — rasio ini
        // tidak berubah walau dihitung dari ha mentah atau km², jadi tidak
        // perlu dikonversi di sini.

        $trendRows = DB::table('vw_overlay_spasial')
            ->whereIn('tahun', $availableYears)
            ->get()
            ->groupBy('tahun');

        $trendData = [
            'years' => [],
            'rth' => [],
            'jumlah_penduduk' => []
        ];

        foreach ($trendRows as $tahun => $items) {

            $totalRthTahun = $items->sum('luas_rth');

            $totalLuasTahun = $items->sum('luas_kelurahan');

            $totalPendudukTahun = $items->sum('jumlah_penduduk');

            $trendData['years'][] = $tahun;

            $trendData['rth'][] = $totalLuasTahun > 0
                ? round(($totalRthTahun / $totalLuasTahun) * 100, 2)
                : 0;

            $totalPendudukTahun = $items->sum('jumlah_penduduk');
            $totalLuasTahun     = $items->sum('luas_kelurahan');

            $trendData['jumlah_penduduk'][] =
            round($totalPendudukTahun);

        }

        // GeoJSON: tampilkan polygon dari semua kelurahan,
        // lalu ambil nilai overlay untuk tahun terpilih agar peta punya polygon & warna.
        $geoJsonResult = DB::selectOne("
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', json_agg(
                    json_build_object(
                        'type', 'Feature',
                        'geometry', ST_AsGeoJSON(geom)::json,
                        'properties', json_build_object(
                            'gid', gid,
                            'nama', namobj,
                            'kecamatan', wadmkc,
                            'persen_rth', ROUND(
                                CASE WHEN COALESCE(luas_kelurahan, 0) > 0
                                     THEN (COALESCE(luas_rth, 0) / luas_kelurahan) * 100
                                     ELSE 0
                                END::numeric, 4
                            ),
                            'luas_rth_km2', ROUND(COALESCE(luas_rth,0)::numeric,6),
                            'luas_kelurahan_km2', ROUND(COALESCE(luas_kelurahan,0)::numeric,3),
                            'kepadatan', ROUND(COALESCE(kepadatan_penduduk,0)::numeric),
                            'jumlah_penduduk', ROUND(COALESCE(jumlah_penduduk,0)::numeric),
                            'rth_per_kapita', ROUND(
                                CASE WHEN COALESCE(jumlah_penduduk, 0) > 0
                                     THEN (COALESCE(luas_rth, 0) * 1000000) / jumlah_penduduk
                                     ELSE 0
                                END::numeric, 4
                            ),
                            'memenuhi_standar_kelurahan', (
                                COALESCE(jumlah_penduduk, 0) > 0
                                AND (COALESCE(luas_rth, 0) * 1000000) / NULLIF(jumlah_penduduk, 0) >= 0.30
                                AND COALESCE(luas_rth, 0) * 1000000 >= 9000
                            )
                        )
                    )
                )
            ) AS geojson
            FROM vw_overlay_spasial
            WHERE tahun = ?
        ", [$selectedYear]);

        $mapGeoJson = json_decode($geoJsonResult->geojson, true);

        foreach ($mapGeoJson['features'] as &$feature) {

            $gid = $feature['properties']['gid'];

            $feature['properties']['skor_prioritas'] =
                $priorityScores[$gid] ?? 0;

        }
        unset($feature);

        $masterData = $rows->map(function ($r) use ($priorityScores) {

            return [

                'gid' => $r->gid,

                'nama' => $r->namobj,

                'kecamatan' => $r->wadmkc,

                'persen_rth' => $r->persentase_rth,

                'luas_rth' => $r->luas_rth_km2,

                'luas_kelurahan' => $r->luas_kelurahan_km2,

                'kepadatan' => $r->kepadatan_penduduk,

                'jumlah_penduduk' => $r->jumlah_penduduk,

                'rth_per_kapita' => $r->rth_per_kapita,

                'memenuhi_standar_kelurahan' => $r->memenuhi_standar_kelurahan,

                'skor_prioritas' => $priorityScores[$r->gid] ?? 0,

                // ── Analisis perubahan luas RTH vs tahun pembanding ──
                'diff_persen_rth' => $r->diff_persen_rth,

                'diff_luas_rth_km2' => $r->diff_luas_rth_km2,

                'trend_rth' => $r->trend_rth,

            ];

        });

        return view('data', compact(
            'selectedYear',
            'availableYears',
            'stats',
            'priorityData',
            'pieData',
            'kategoriDist',
            'rthBarData',
            'popData',
            'rthChangeData',
            'trendData',
            'mapGeoJson',
            'masterData'
        ));
    }
}