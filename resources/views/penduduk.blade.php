{{-- resources/views/admin/penduduk/index.blade.php --}}
@extends('layouts.admin')
@section('page-title', 'Dashboard Admin')
@section('title', 'Admin SIG RTH Publik Kota Bekasi - Kepadatan Penduduk')
@section('admin-content')
{{-- ── Stat Cards ──────────────────────────────── --}}
<div class="stat-grid">
    <x-admin.stat-card
        icon="database"
        label="Data"
        :value="$stats['total'] . ' entri'"
        :sub="'Total: ' . $stats['total']"
    />
    <x-admin.stat-card
        icon="users"
        label="Total Kepadatan"
        :value="number_format($stats['total_kepadatan'])"
        sub="jiwa/km²"
    />
    <x-admin.stat-card
        icon="calendar"
        label="Tahun Tersedia"
        :value="(string) $stats['jumlah_tahun']"
        sub="Data tersedia"
    />
</div>
{{-- ── Toolbar / Panel ──────────────────────────── --}}
<x-admin.toolbar
    add-label="Tambah Data Kepadatan Penduduk"
    add-modal="modal-tambah-penduduk"
/>
{{-- ── Filter Bar ──────────────────────────────── --}}
<div>
    <x-admin.filter-bar
        searchId="pendudukSearch"
        searchPlaceholder="Cari kelurahan..."
        searchAction="filterPendudukTable()"
        :filters="[
            [
                'id' => 'pendudukTahun',
                'action' => 'filterPendudukTable()',
                'options' => collect(['' => 'Semua Tahun'])->merge(
                    collect($years)->mapWithKeys(fn($y) => [$y => $y])
                )->toArray()
            ],
            [
                'id' => 'pendudukStatus',
                'action' => 'filterPendudukTable()',
                'options' => [
                    '' => 'Semua Status',
                    'Rendah' => 'Rendah',
                    'Sedang' => 'Sedang',
                    'Tinggi' => 'Tinggi',
                    'Sangat Padat' => 'Sangat Padat',
                ]
            ]
        ]"
    />
</div>
{{-- ── Tabel Kepadatan Penduduk ──────────────── --}}
<div class="table-card">
    <div class="table-wrap">
        <table class="data-table" id="tbl-penduduk">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kelurahan</th>
                    <th>Tahun</th>
                    <th>Jumlah Penduduk</th>
                    <th>Kepadatan (jiwa/km²)</th>
                    <th>Kepadatan (jiwa/ha)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $row = (object) $row;
                        $statusRow = $row->status_kepadatan ?? ($row->status ?? '');
                        $kepadatanVal = $row->kepadatan_penduduk ?? ($row->kepadatan ?? 0);
                        $kepadatanHaVal = $row->kepadatan_ha ?? (is_numeric($kepadatanVal) ? round($kepadatanVal / 100, 2) : 0);
                        static $no = 0;
                        $no++;
                    @endphp
                    <tr data-kec="{{ strtolower($row->namobj) }}" data-tahun="{{ $row->tahun }}" data-status="{{ $statusRow }}">
                        <td data-label="No">{{ $no }}</td>
                        <td data-label="Kelurahan">{{ $row->namobj }}</td>
                        <td data-label="Tahun">{{ $row->tahun }}</td>
                        <td data-label="Jumlah Penduduk">{{ number_format($row->jumlah_penduduk ?? 0) }}</td>
                        <td data-label="Kepadatan (jiwa/km²)">{{ number_format($kepadatanVal) }}</td>
                        <td data-label="Kepadatan (jiwa/ha)">{{ number_format($kepadatanHaVal, 2) }}</td>
                        <td data-label="Status">
                            <x-admin.badge :status="$statusRow" />
                        </td>
                        <td data-label="Aksi">
                            <x-admin.action-buttons
                                edit-modal="modal-edit-penduduk"
                                :edit-fields="[
                                    'id' => $row->id,
                                    'gid' => $row->gid,
                                    'tahun' => $row->tahun,
                                    'namobj' => $row->namobj,
                                    'wadmkc' => $row->wadmkc ?? '',
                                    'jumlah_penduduk' => $row->jumlah_penduduk ?? '',
                                    'kepadatan_penduduk' => $row->kepadatan_penduduk,
                                ]"
                                delete-modal="modal-delete"
                                :delete-url="route('admin.kepadatan.destroy', $row->id)"
                                :delete-label="$row->namobj . ' (' . $row->tahun . ')'"
                            />
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="8">Tidak ada data yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{-- ── Modal Tambah Penduduk ──────────────────── --}}
@php
    $tahunOptionsKp = collect($years)
        ->push((int) date('Y'))
        ->push((int) date('Y') + 1)
        ->unique()
        ->sortDesc()
        ->values();
@endphp
<x-admin.modal id="modal-tambah-penduduk" title="Tambah Data Penduduk" subtitle="Lengkapi data kepadatan penduduk per kelurahan" icon="form">
    <form method="POST" action="{{ route('admin.kepadatan.store') }}">
        @csrf
        <div class="form-section-label">Lokasi</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Kelurahan <span style="color:var(--danger, #dc2626)">*</span></label>
                <select name="gid" class="form-input" data-kelurahan-select required onchange="syncKecamatan(this); previewJumlahPenduduk(this.closest('.modal'))">
                    <option value="" disabled selected>Pilih kelurahan…</option>
                    @foreach($kelurahan as $k)
                        <option value="{{ $k->gid }}" data-wadmkc="{{ $k->wadmkc ?? '' }}">{{ $k->namobj }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kecamatan</label>
                <input type="text" name="wadmkc" class="form-input form-computed" data-kecamatan-field readonly placeholder="Terisi otomatis">
            </div>
        </div>
        <div class="form-section-label">Data Tahunan</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tahun <span style="color:var(--danger, #dc2626)">*</span></label>
                <select name="tahun" class="form-input" required onchange="previewJumlahPenduduk(this.closest('.modal'))">
                    @foreach($tahunOptionsKp as $ty)
                        <option value="{{ $ty }}" {{ $ty == date('Y') ? 'selected' : '' }}>{{ $ty }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Jumlah Penduduk (jiwa)</label>
                <input type="text" class="form-input form-computed" data-jumlah-preview readonly placeholder="Otomatis dihitung">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Kepadatan Penduduk (jiwa/km²) <span style="color:var(--danger, #dc2626)">*</span></label>
            <input name="kepadatan_penduduk" type="number" step="1" class="form-input" placeholder="mis: 2500" required oninput="previewJumlahPenduduk(this.closest('.modal'))">
            <div class="form-hint">Jumlah Penduduk dihitung otomatis: Kepadatan × Luas Kelurahan (data RTH tahun terkait). Jika data RTH kelurahan/tahun ini belum ada, nilainya akan 0.</div>
        </div>
        <x-slot:footer>
            <button type="button" class="btn btn--outline" data-modal-close="modal-tambah-penduduk">Batal</button>
            <button type="submit" class="btn btn--primary">Simpan</button>
        </x-slot:footer>
    </form>
</x-admin.modal>
{{-- ── Modal Edit Penduduk ────────────────────── --}}
<x-admin.modal id="modal-edit-penduduk" title="Edit Data Penduduk" subtitle="Perbarui data kepadatan penduduk" icon="edit">
    <form method="POST" action="" id="form-edit-penduduk">
        @csrf
        @method('PUT')
        <input type="hidden" name="id">
        <div class="form-section-label">Lokasi</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Kelurahan <span style="color:var(--danger, #dc2626)">*</span></label>
                <select name="gid" class="form-input" data-kelurahan-select required onchange="syncKecamatan(this); previewJumlahPenduduk(this.closest('.modal'))">
                    @foreach($kelurahan as $k)
                        <option value="{{ $k->gid }}" data-wadmkc="{{ $k->wadmkc ?? '' }}">{{ $k->namobj }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kecamatan</label>
                <input type="text" name="wadmkc" class="form-input form-computed" data-kecamatan-field readonly placeholder="Terisi otomatis">
            </div>
        </div>
        <div class="form-section-label">Data Tahunan</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tahun <span style="color:var(--danger, #dc2626)">*</span></label>
                <select name="tahun" class="form-input" required onchange="previewJumlahPenduduk(this.closest('.modal'))">
                    @foreach($tahunOptionsKp as $ty)
                        <option value="{{ $ty }}">{{ $ty }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Jumlah Penduduk (jiwa)</label>
                <input type="text" class="form-input form-computed" data-jumlah-preview readonly placeholder="Otomatis dihitung">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Kepadatan Penduduk (jiwa/km²) <span style="color:var(--danger, #dc2626)">*</span></label>
            <input name="kepadatan_penduduk" type="number" step="1" class="form-input" required oninput="previewJumlahPenduduk(this.closest('.modal'))">
            <div class="form-hint">Jumlah Penduduk dihitung otomatis: Kepadatan × Luas Kelurahan (data RTH tahun terkait). Jika data RTH kelurahan/tahun ini belum ada, nilainya akan 0.</div>
        </div>
        <x-slot:footer>
            <button type="button" class="btn btn--outline" data-modal-close="modal-edit-penduduk">Batal</button>
            <button type="submit" class="btn btn--primary">Update</button>
        </x-slot:footer>
    </form>
</x-admin.modal>
{{-- ── Modal Hapus ──────────────────────────────── --}}
<x-admin.delete-modal id="modal-delete" />
@once
<style>
.form-section-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
    color: var(--muted, #6b7280); margin-bottom: 12px; margin-top: 4px; padding-bottom: 8px;
    border-bottom: 1px solid var(--border, #dde5df);
}
.form-section-label + .form-row,
.form-section-label + .form-group {
    margin-top: 0;
}
.form-group { margin-bottom: 16px; }
.form-label { display: flex; align-items: center; font-size: 12.5px; font-weight: 600; color: var(--text-2, #374151); margin-bottom: 6px; }
.form-label span { color: var(--danger, #dc2626); margin-left: 2px; }
.field-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; border-radius: 6px; margin-right: 7px;
    flex-shrink: 0; color: #fff;
}
.field-badge--pin      { background: linear-gradient(150deg, #1a6b4a, #2f8f63); }
.field-badge--map      { background: linear-gradient(150deg, #7c3aed, #a78bfa); }
.field-badge--calendar { background: linear-gradient(150deg, #a21caf, #d946ef); }
.field-badge--users    { background: linear-gradient(150deg, #2563eb, #60a5fa); }
.field-badge--density  { background: linear-gradient(150deg, #c2410c, #fb923c); }
.form-input {
    width: 100%; padding: 10px 13px;
    border: 1.5px solid var(--border, #dde5df); border-radius: var(--r-sm, 8px);
    font-size: 13.5px; background: var(--surface, #fff);
    outline: none; transition: border-color .12s ease, box-shadow .12s ease;
    color: var(--text, #111827); font-family: inherit;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
}
.form-input::placeholder { color: var(--muted-lt, #9ca3af); }
.form-input:hover { border-color: var(--border-md, #c8d6cb); }
.form-input:focus {
    border-color: var(--primary, #1a6b4a);
    box-shadow: 0 0 0 3px rgba(26,107,74,.12);
}
.form-input[readonly] {
    background: var(--bg, #f0f4f1); color: var(--muted, #6b7280);
    cursor: default; border-style: dashed;
}
.form-hint { font-size: 11.5px; color: var(--muted, #6b7280); margin-top: 5px; }
.form-error { font-size: 12px; color: var(--danger, #dc2626); margin-top: 5px; font-weight: 500; }
.form-computed {
    background: var(--primary-xl, #f0faf5) !important;
    border-color: #c3dfd0 !important;
    border-style: solid !important;
    color: var(--primary, #1a6b4a) !important;
    font-weight: 600 !important;
}
</style>
@endonce
@endsection
@section('extra-styles')
<style>
    /* ── Stat cards: fluid grid, wraps naturally ── */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }
    /* ── Filter bar: stack search + selects on small screens ── */
    @media (max-width: 768px) {
        .filter-bar,
        [class*="filter-bar"] {
            flex-wrap: wrap;
        }
        .filter-bar > *,
        [class*="filter-bar"] > * {
            width: 100%;
        }
    }
    /* ── Toolbar: button wraps under label on narrow screens ── */
    @media (max-width: 576px) {
        .toolbar,
        [class*="toolbar"] {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
    }
    /* ── Table card wrapper ──────────────────────────────────── */
    .table-card {
        width: 100%;
        overflow: hidden;
    }
    .table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .data-table {
        width: 100%;
        min-width: 640px; /* keeps columns readable while horizontal-scrolling on tablets */
        border-collapse: collapse;
    }
    /* ── Mobile: convert table rows into stacked cards ──────
       Below 576px a horizontally-scrolling table becomes hard
       to read, so we flip to a labeled card layout instead. ── */
    @media (max-width: 576px) {
        .data-table,
        .data-table thead,
        .data-table tbody,
        .data-table tr,
        .data-table td {
            display: block;
            width: 100%;
        }
        .data-table {
            min-width: 0;
        }
        .data-table thead {
            display: none; /* labels come from data-label instead */
        }
        .data-table tbody tr {
            margin-bottom: 0.75rem;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: var(--surface, #fff);
        }
        .data-table tbody tr.empty-row {
            border: none;
            text-align: center;
            padding: 1.5rem 0.75rem;
        }
        .data-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            border: none;
            border-bottom: 1px dashed var(--border, #e2e8f0);
            text-align: right;
        }
        .data-table td:last-child {
            border-bottom: none;
        }
        .data-table td[data-label]::before {
            content: attr(data-label);
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--text-muted, #64748b);
            text-align: left;
            flex-shrink: 0;
        }
        .data-table td[data-label="Aksi"] {
            justify-content: flex-end;
        }
        .data-table td[data-label="Aksi"]::before {
            display: none;
        }
    }
    /* ── Modal forms: 2-col rows collapse to 1 col on mobile ── */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .form-row .form-group {
        flex: 1 1 220px;
        min-width: 0;
    }
    @media (max-width: 480px) {
        .form-row .form-group {
            flex: 1 1 100%;
        }
    }
    /* ── Action buttons: don't overflow on narrow cards ── */
    [class*="action-buttons"] {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        justify-content: flex-end;
    }
</style>
@endsection
@section('scripts')
<script>
/* =====================================================
   PETA LUAS KELURAHAN (gid-tahun → km²)
   Dipakai untuk preview "Jumlah Penduduk" di modal,
   memakai rumus yang sama persis dengan AdminController:
   jumlah_penduduk = kepadatan_penduduk × luas_kelurahan(km²)
===================================================== */
window.__LUAS_KELURAHAN__ = @json($luasKelurahanMap);
function previewJumlahPenduduk(modal) {
    if (!modal) return;
    const previewEl = modal.querySelector('[data-jumlah-preview]');
    if (!previewEl) return;
    const gid = modal.querySelector('[name="gid"]')?.value;
    const tahun = modal.querySelector('[name="tahun"]')?.value;
    const kepadatan = parseFloat(modal.querySelector('[name="kepadatan_penduduk"]')?.value) || 0;
    const luasKm2 = window.__LUAS_KELURAHAN__?.[`${gid}-${tahun}`];
    if (!gid || !tahun || luasKm2 === undefined) {
        previewEl.value = luasKm2 === undefined && gid && tahun
            ? '0 (data RTH kelurahan/tahun ini belum ada)'
            : '';
        return;
    }
    const jumlah = Math.round(kepadatan * luasKm2);
    previewEl.value = jumlah.toLocaleString('id-ID') + ' jiwa';
}
/* =====================================================
   CSRF TOKEN
===================================================== */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}
/* =====================================================
   TABLE FILTER
===================================================== */
function filterPendudukTable() {
    const q = (document.getElementById('pendudukSearch')?.value ?? '')
        .toLowerCase()
        .trim();
    const tahun = document.getElementById('pendudukTahun')?.value ?? '';
    const status = document.getElementById('pendudukStatus')?.value ?? '';
    document
        .querySelectorAll('#tbl-penduduk tbody tr:not(.empty-row)')
        .forEach(tr => {
            const kec = (tr.dataset.kec ?? '').toLowerCase();
            const t = String(tr.dataset.tahun ?? '');
            const st = tr.dataset.status ?? '';
            const show = (!q || kec.includes(q))
                && (!tahun || t === tahun)
                && (!status || st === status);
            tr.style.display = show ? '' : 'none';
        });
    const emptyRow = document.querySelector('#tbl-penduduk .empty-row');
    if (emptyRow) {
        const visible = Array.from(
            document.querySelectorAll('#tbl-penduduk tbody tr:not(.empty-row)')
        ).some(tr => tr.style.display !== 'none');
        emptyRow.style.display = visible ? 'none' : '';
    }
}
/* =====================================================
   KECAMATAN AUTOFILL
   Mengisi field Kecamatan otomatis berdasarkan atribut
   data-wadmkc pada opsi Kelurahan yang dipilih.
===================================================== */
function syncKecamatan(selectEl) {
    const modal = selectEl.closest('.modal');
    if (!modal) return;
    const kecField = modal.querySelector('[data-kecamatan-field]');
    if (!kecField) return;
    const opt = selectEl.options[selectEl.selectedIndex];
    kecField.value = opt?.dataset?.wadmkc ?? '';
}
/* =====================================================
   EDIT MODAL (SAMA DENGAN RTH)
===================================================== */
function openEditModal(modalId, data) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    const set = (name, value) => {
        const el = modal.querySelector(`[name="${name}"]`);
        if (!el) return;
        el.value = value ?? '';
        // Selects are backed by a custom dropdown UI (see modal.blade.php)
        // that listens for 'change' to keep its display in sync.
        if (el.tagName === 'SELECT') {
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };
    set('id', data.id);
    set('gid', data.gid);
    set('tahun', data.tahun);
    set('kepadatan_penduduk', data.kepadatan_penduduk);
    // Kecamatan mengikuti kelurahan yang baru saja di-set
    const gidSelect = modal.querySelector('[data-kelurahan-select]');
    if (gidSelect) syncKecamatan(gidSelect);
    // Preview jumlah penduduk mengikuti data terbaru (kepadatan × luas RTH)
    previewJumlahPenduduk(modal);
    // update form action
    const form = modal.querySelector('form');
    if (form && data.id) {
        form.action = `/admin/kepadatan/${data.id}`;
    }
    modal.classList.add('open');
}
/* =====================================================
   RESET MODAL
===================================================== */
function resetPendudukModal(modal) {
    modal.querySelectorAll('input').forEach(el => {
        el.value = '';
    });
    modal.querySelectorAll('select').forEach(el => {
        el.selectedIndex = 0;
        el.dispatchEvent(new Event('change', { bubbles: true }));
    });
}
/* =====================================================
   DELETE MODAL (SAMA DENGAN RTH)
===================================================== */
function confirmDelete(modalId, url, label) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    const labelEl = modal.querySelector('[data-delete-label]');
    if (labelEl) {
        labelEl.textContent = label;
    }
    const confirmBtn = modal.querySelector('[data-delete-confirm]');
    if (confirmBtn) {
        confirmBtn.onclick = () => doDelete(url, modalId);
    }
    modal.classList.add('open');
}
/* =====================================================
   DELETE ACTION
===================================================== */
async function doDelete(url, modalId) {
    try {
        const res = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken()
            }
        });
        const data = await res.json();
        document.getElementById(modalId)?.classList.remove('open');
        if (data.success) {
            location.reload();
        } else {
            alert(data.message ?? 'Gagal menghapus data');
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan');
    }
}
/* =====================================================
   CLOSE MODAL
===================================================== */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modalClose);
            if (modal) {
                modal.classList.remove('open');
                resetPendudukModal(modal);
            }
        });
    });
    // klik luar modal
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) {
                overlay.classList.remove('open');
            }
        });
    });
    // escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.open').forEach(modal => {
                modal.classList.remove('open');
            });
        }
    });
});
</script>
@endsection