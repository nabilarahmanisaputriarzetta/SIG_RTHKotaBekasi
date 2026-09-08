<?php

namespace App\Http\Controllers;

use App\Models\OverlaySpasial;

class HomeController extends Controller
{
    public function index()
    {
        // Semua tahun yang tersedia di DB, diurutkan ascending
        // Catatan: sumber data disamakan dengan DataController & PetaController
        // (vw_overlay_spasial) supaya angka RTH kota konsisten di semua halaman.
        $allYears = OverlaySpasial::select('tahun')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun')
            ->toArray();

        // Hitung summary untuk setiap tahun
        $summaryByYear = [];
        foreach ($allYears as $yr) {
            $summaryByYear[$yr] = $this->calcSummary($yr);
        }

        // $summary = data tahun terbaru (untuk backward-compat view lama)
        $tahun   = end($allYears);
        $summary = $summaryByYear[$tahun];

        return view('home', compact('summary', 'summaryByYear', 'tahun'));
    }

    private function calcSummary(int $tahun): array
    {
        $rows = OverlaySpasial::where('tahun', $tahun)->get();

        $processed = $rows->map(function ($r) {
            // vw_overlay_spasial sudah mengembalikan luas_rth & luas_kelurahan
            // dalam km² langsung (dikonversi dari m² di level SQL: ÷1.000.000),
            // jadi tidak perlu dikonversi lagi di sini. Konsisten dengan
            // DataController & PetaController::apiRth.
            $luasRthKm2       = (float) ($r->luas_rth ?? 0);
            $luasKelurahanKm2 = (float) ($r->luas_kelurahan ?? 0);

            // Pakai persentase_rth langsung dari view (hasil overlay GIS),
            // bukan dihitung ulang, supaya sama persis dengan yang tampil
            // di halaman Data & Peta.
            $persentaseRth = (float) ($r->persentase_rth ?? 0);

            $targetRthKm2 = (($r->jumlah_penduduk ?? 0) * 0.30) / 1000000;

            return [
                'kelurahan'      => strtoupper(trim($r->namobj)),
                'luas_rth'       => $luasRthKm2,
                'luas_kelurahan' => $luasKelurahanKm2,
                'persentase_rth' => $persentaseRth,

                'target_rth'     => $targetRthKm2,

                'status' => $luasRthKm2 >= $targetRthKm2
                    ? 'Memenuhi'
                    : 'Belum Memenuhi',
            ];
        });

        $memenuhi         = $processed->where('status', 'Memenuhi')->count();
        $dibawah          = $processed->where('status', 'Belum Memenuhi')->count();
        $summaryTotalRth  = round($processed->sum('luas_rth'), 2);
        $totalWilayahKota = round($processed->sum('luas_kelurahan'), 2);
        $pctKota          = $totalWilayahKota > 0
            ? round(($summaryTotalRth / $totalWilayahKota) * 100, 2)
            : 0;
        $statusKota       = $pctKota >= 20 ? 'Memenuhi' : 'Belum Memenuhi';

        return [
            'memenuhi'        => $memenuhi,
            'dibawah'         => $dibawah,
            'totalRth'        => $summaryTotalRth,
            'totalWilayah'    => $totalWilayahKota,
            'pctKota'         => $pctKota,
            'statusKota'      => $statusKota,
            'jumlahKelurahan' => $processed->count(),
        ];
    }
}