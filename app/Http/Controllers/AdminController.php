<?php

namespace App\Http\Controllers;

use App\Models\KepadatanPenduduk;
use App\Models\RthKelurahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.rth.index');
    }

    // ─── RTH ──────────────────────────────────────────────────────────────────

    public function rthIndex(Request $request)
    {
        $query = RthKelurahan::query();

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        if ($request->filled('search')) {
            $query->where('namobj', 'like', '%' . $request->search . '%');
        }

        $rthRows = $query
            ->orderBy('tahun', 'desc')
            ->orderBy('namobj')
            ->get();

        // ── Data kepadatan diambil sekali buat semua baris (bukan query per
        //    baris), dikunci pasangan gid+tahun sama kayak RTH-nya, supaya
        //    jumlah penduduk & RTH per kapita bisa dihitung. Pola ini sama
        //    persis dengan PetaController::apiOverlay. ──
        $kepadatanByKey = KepadatanPenduduk::all()
            ->keyBy(fn ($k) => $k->gid . '-' . $k->tahun);

        $rows = $rthRows->map(function ($r) use ($kepadatanByKey) {
            $luasRth = (float) ($r->luas_rth ?? 0) / 1000000;
            $luasWilayah = (float) ($r->luas_kelurahan ?? 0) / 1000000;

            $pct = $luasWilayah > 0
                ? round(($luasRth / $luasWilayah) * 100, 4)
                : 0;

            // ── Status per kelurahan: Permen PU No.05/PRT/M/2008 (RTH per
            //    kapita ≥ 0,30 m²/jiwa DAN luas RTH ≥ 9.000 m²) — bukan
            //    threshold 20% (itu cuma berlaku buat agregat kota, lihat
            //    PetaController::apiRth/apiOverlay untuk penjelasan yang sama). ──
            $kep = $kepadatanByKey->get($r->gid . '-' . $r->tahun);
            $kepadatanKm2 = (float) ($kep->kepadatan_penduduk ?? 0);
            $jumlahPenduduk = $kepadatanKm2 > 0 && $luasWilayah > 0
                ? (int) round($kepadatanKm2 * $luasWilayah)
                : 0;

            $luasRthM2 = $luasRth * 1000000;
            $rthPerKapita = $jumlahPenduduk > 0 ? $luasRthM2 / $jumlahPenduduk : 0;
            $memenuhi = $jumlahPenduduk > 0 && $rthPerKapita >= 0.30 && $luasRthM2 >= 9000;
            $status = $memenuhi ? 'Memenuhi' : 'Belum Memenuhi';

            return [
                'id' => $r->id,
                'gid' => $r->gid,
                'kelurahan' => $r->namobj,
                'kecamatan' => $r->wadmkc,
                'tahun' => $r->tahun,
                'luas_kelurahan' => $luasWilayah,
                'luas_rth' => $luasRth,
                'persentase_rth' => $pct,
                'jumlah_penduduk' => $jumlahPenduduk,
                'rth_per_kapita' => round($rthPerKapita, 4),
                'status' => $status,
            ];
        });

        if ($request->filled('status')) {
            $rows = $rows->filter(fn ($r) => $r['status'] === $request->status)->values();
        }

        $years = RthKelurahan::select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();

        $totalRth = $rows->sum('luas_rth');
        $totalWil = $rows->sum('luas_kelurahan');
        $avgPct = $rows->avg('persentase_rth');
        $entryCount = $rows->count();

        $kelurahan = DB::table('kelurahan')
            ->select('gid', 'namobj', 'wadmkc')
            ->orderBy('namobj')
            ->get();

        return view('rth', compact(
            'rows',
            'years',
            'totalRth',
            'totalWil',
            'avgPct',
            'entryCount',
            'kelurahan'
        ));
    }

    public function rthStore(Request $request)
    {
        $data = $request->validate([
            'gid' => 'required|integer',
            'tahun' => 'required|integer|min:2000|max:2100',
            'luas_kelurahan' => 'required|numeric|min:0',
            'luas_rth' => 'required|numeric|min:0',
        ]);

        // Input dari form dalam km²
        $luasKelurahanKm2 = (float) $data['luas_kelurahan'];
        $luasRthKm2 = (float) $data['luas_rth'];

        // Persentase dihitung dari km² (hasil tetap benar)
        $persentase = $luasKelurahanKm2 > 0
            ? ($luasRthKm2 / $luasKelurahanKm2) * 100
            : 0;

        // Simpan ke database dalam m²
        $luasKelurahan = $luasKelurahanKm2 * 1000000;
        $luasRth = $luasRthKm2 * 1000000;

        $kel = DB::table('kelurahan')
            ->select('namobj', 'wadmkc')
            ->where('gid', $data['gid'])
            ->first();

        if (!$kel) {
            return response()->json([
                'success' => false,
                'message' => 'Kelurahan tidak ditemukan.',
            ], 404);
        }

        // Pakai namobj+tahun sebagai key, bukan gid (gid tidak dikirim dari form)
        RthKelurahan::updateOrCreate(
            [
                'gid' => $data['gid'],
                'tahun' => $data['tahun'],
            ],
            [
                'gid' => $data['gid'],
                'namobj' => $kel->namobj,
                'wadmkc' => $kel->wadmkc,
                'luas_kelurahan' => $luasKelurahan,
                'luas_rth' => $luasRth,
                'persentase_rth' => round($persentase, 4),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Data RTH berhasil disimpan.']);
    }

    public function rthUpdate(Request $request, int $id)
    {
        $data = $request->validate([
            'gid' => 'required|integer',
            'tahun' => 'required|integer',
            'luas_kelurahan' => 'required|numeric',
            'luas_rth' => 'required|numeric',
        ]);

        $luasKelurahanKm2 = (float) $data['luas_kelurahan'];
        $luasRthKm2 = (float) $data['luas_rth'];

        $persentase = $luasKelurahanKm2 > 0
            ? ($luasRthKm2 / $luasKelurahanKm2) * 100
            : 0;

        $luasKelurahan = $luasKelurahanKm2 * 1000000;
        $luasRth = $luasRthKm2 * 1000000;

        $kel = DB::table('kelurahan')
            ->select('namobj', 'wadmkc')
            ->where('gid', $data['gid'])
            ->first();

        if (!$kel) {
            return response()->json([
                'success' => false,
                'message' => 'Kelurahan tidak ditemukan.',
            ], 404);
        }

        $row = RthKelurahan::findOrFail($id);
        $row->update([
            'gid' => $data['gid'],
            'namobj' => $kel->namobj,
            'wadmkc' => $kel->wadmkc,
            'tahun' => $data['tahun'],
            'luas_kelurahan' => $luasKelurahan,
            'luas_rth' => $luasRth,
            'persentase_rth' => round($persentase, 4),
        ]);

        return response()->json(['success' => true, 'message' => 'Data RTH berhasil diperbarui.']);
    }

    public function rthDestroy(int $id)
    {
        RthKelurahan::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Data RTH berhasil dihapus.']);
    }

    // ─── Kepadatan ────────────────────────────────────────────────────────────

    public function kepadatanIndex(Request $request)
    {
        $query = KepadatanPenduduk::query();
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        if ($request->filled('search')) {
            $query->where('namobj', 'ilike', '%' . $request->search . '%');
        }

        $rows = $query
            ->orderBy('tahun', 'desc')
            ->orderBy('namobj')
            ->get()
            ->map(function ($r) {
                $arr = $r->toArray();
                // Kolom kepadatan_penduduk tersimpan jiwa/km² (sama seperti
                // di vw_overlay_spasial) — dikonversi ke jiwa/ha (÷100) dulu
                // sebelum diklasifikasi, karena SNI 03-1733-2004 memang
                // berbasis per hektare. Status SELALU dihitung ulang dari
                // sini (bukan baca kolom status_kepadatan lama di DB), sama
                // seperti kenapa persentase_rth juga dihitung ulang di
                // rthIndex() — supaya tidak ada nilai tersimpan yang basi.
                $klasifikasi = KepadatanPenduduk::classifyKepadatan((float) ($r->kepadatan_penduduk ?? 0));

                return array_merge($arr, [
                    'kepadatan_ha'     => $klasifikasi['ha'],
                    'status_kepadatan' => $klasifikasi['status'], 
                ]);
            });

        $years = KepadatanPenduduk::select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();

        $totalKepadatan = $rows->sum('kepadatan_penduduk');
        $entryCount = $rows->count();

        $stats = [
            'total' => $entryCount,
            'total_kepadatan' => $totalKepadatan,
            'jumlah_tahun' => count($years),
        ];

        // ── Sama seperti rthIndex(): dropdown "Kelurahan" di modal Tambah/Edit
        //    harus pilih dari daftar kelurahan Kota Bekasi yang resmi (tabel
        //    kelurahan), bukan ketik bebas — supaya gid yang dikirim selalu
        //    valid dan namobj-nya konsisten dengan data RTH. ──
        $kelurahan = DB::table('kelurahan')
            ->select('gid', 'namobj', 'wadmkc')
            ->orderBy('namobj')
            ->get();

        // ── Jumlah Penduduk tidak diinput manual (dihitung server dari
        //    kepadatan × luas_kelurahan RTH, lihat kepadatanStore/Update).
        //    Peta gid+tahun → luas_kelurahan (km²) ini dikirim ke view supaya
        //    modal bisa menampilkan preview "Jumlah Penduduk" secara live
        //    tanpa perlu roundtrip ke server, memakai rumus yang sama persis
        //    dengan yang dipakai di controller saat menyimpan. ──
        $luasKelurahanMap = RthKelurahan::select('gid', 'tahun', 'luas_kelurahan')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->gid . '-' . $r->tahun => round(((float) $r->luas_kelurahan) / 1000000, 6),
            ]);

        return view('penduduk', compact(
            'rows',
            'years',
            'entryCount',
            'stats',
            'kelurahan',
            'luasKelurahanMap'
        ));
    }

    public function kepadatanStore(Request $request)
    {
        $data = $request->validate([
            'gid' => 'required|integer',
            'tahun' => 'required|integer|min:2000|max:2100',
            'kepadatan_penduduk' => 'required|numeric|min:0',
        ]);

        // namobj & wadmkc diturunkan dari FK (gid)
        // Asumsi: kolom `namobj` dan `wadmkc` tersedia di tabel referensi kelurahan (FK).
        // Jika nama tabel referensi berbeda, sesuaikan di sini.
        $kel = DB::table('kelurahan')
            ->select(['namobj', 'wadmkc'])
            ->where('gid', $data['gid'])
            ->first();

        $namobj = $kel?->namobj;
        $wadmkc = $kel?->wadmkc;

        // jumlah_penduduk tidak diinput manual di form (lihat penduduk_blade.php),
        // jadi diturunkan dari kepadatan × luas_kelurahan (km²), mengikuti pola
        // yang sudah dipakai di PetaController::apiKepadatan & apiRth, supaya
        // konsisten dan tidak perlu duplikasi input di UI.
        $rth = RthKelurahan::where('gid', $data['gid'])
            ->where('tahun', $data['tahun'])
            ->first();

        $jumlahPenduduk = 0;
        if ($rth && (float) $rth->luas_kelurahan > 0) {
            // luas_kelurahan di data_rth_kelurahan_publik disimpan dalam m².
            $luasKelurahanKm2 = (float) $rth->luas_kelurahan / 1000000;
            $jumlahPenduduk = (int) round($data['kepadatan_penduduk'] * $luasKelurahanKm2);
        }

        KepadatanPenduduk::updateOrCreate(
            ['gid' => $data['gid'], 'tahun' => $data['tahun']],
            [
                'gid' => $data['gid'],
                'tahun' => $data['tahun'],
                'namobj' => $namobj,
                'wadmkc' => $wadmkc,
                'kepadatan_penduduk' => $data['kepadatan_penduduk'],
                'jumlah_penduduk' => $jumlahPenduduk,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Data kepadatan berhasil disimpan.']);
    }

    public function kepadatanUpdate(Request $request, int $id)
    {
        // $id di route adalah id PK pada tabel kepadatan_penduduk.
        $row = KepadatanPenduduk::findOrFail($id);

        $data = $request->validate([
            'gid' => 'required|integer',
            'tahun' => 'required|integer|min:2000|max:2100',
            'kepadatan_penduduk' => 'required|numeric|min:0',
        ]);

        $kel = DB::table('kelurahan')
            ->select(['namobj', 'wadmkc'])
            ->where('gid', $data['gid'])
            ->first();

        $namobj = $kel?->namobj;
        $wadmkc = $kel?->wadmkc;

        // jumlah_penduduk diturunkan dari kepadatan × luas_kelurahan (km²),
        // sama seperti kepadatanStore().
        $rth = RthKelurahan::where('gid', $data['gid'])
            ->where('tahun', $data['tahun'])
            ->first();

        $jumlahPenduduk = 0;
        if ($rth && (float) $rth->luas_kelurahan > 0) {
            $luasKelurahanKm2 = (float) $rth->luas_kelurahan / 1000000;
            $jumlahPenduduk = (int) round($data['kepadatan_penduduk'] * $luasKelurahanKm2);
        }

        $row->update([
            'gid' => $data['gid'],
            'tahun' => $data['tahun'],
            'namobj' => $namobj,
            'wadmkc' => $wadmkc,
            'kepadatan_penduduk' => $data['kepadatan_penduduk'],
            'jumlah_penduduk' => $jumlahPenduduk,
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui.']);
    }

    public function kepadatanDestroy(int $id)
    {
        KepadatanPenduduk::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
    }

    public function kepadatanExport(Request $request)
    {
        $query = KepadatanPenduduk::query();
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        $rows = $query->orderBy('tahun', 'desc')->orderBy('namobj')->get();

        $csv = "Kelurahan,Tahun,Kepadatan Penduduk (jiwa/km2),Kepadatan (jiwa/ha),Status\n";
        foreach ($rows as $r) {
            $klasifikasi = KepadatanPenduduk::classifyKepadatan((float) ($r->kepadatan_penduduk ?? 0));
            $csv .= "{$r->namobj}, {$r->tahun}, {$r->kepadatan_penduduk}, {$klasifikasi['ha']}, {$klasifikasi['status']}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kepadatan_penduduk.csv"',
        ]);
    }
}