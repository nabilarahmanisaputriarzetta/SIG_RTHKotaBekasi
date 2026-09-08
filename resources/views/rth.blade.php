@extends('layouts.admin')
@section('page-title', 'Dashboard Admin')
@section('title', 'Admin SIG RTH Publik Kota Bekasi')
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
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }
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
.field-badge--tree     { background: linear-gradient(150deg, #15803d, #4ade80); }
.field-badge--growth   { background: linear-gradient(150deg, #c2410c, #fb923c); }
.status-preview.is-ok      { color: var(--text-success, #16a34a) !important; border-color: var(--border-success, #86efac) !important; background: var(--bg-success, #f0fdf4) !important; }
.status-preview.is-danger  { color: var(--text-danger, #dc2626) !important; border-color: var(--border-danger, #fca5a5) !important; background: var(--bg-danger, #fef2f2) !important; }
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
@section('admin-content')
{{-- ── RTH PANEL ── --}}
<div id="panelRth">
    {{-- Stats --}}
    <div class="admin-stats">
        <x-admin.stat-card
            icon="database"
            label="Data (filter)"
            :value="$entryCount . ' entri'"
            :sub="'Total: ' . $entryCount"
        />
        <x-admin.stat-card
            icon="tree"
            label="Total RTH"
            :value="number_format($totalRth, 3) . ' km²'"
            :sub="'dari ' . number_format($totalWil, 2) . ' km²'"
        />
        <x-admin.stat-card
            icon="growth"
            label="Rata-rata RTH"
            :value="number_format($avgPct, 2) . '%'"
            sub="Di bawah standar"
        />
        <x-admin.stat-card
            icon="calendar"
            label="Tahun Tersedia"
            :value="count($years)"
            :sub="implode(', ', array_slice($years, 0, 5))"
        />
    </div>
    <x-admin.toolbar
        add-label="Tambah Data RTH"
        add-modal="rthModal"
    />
    {{-- Table --}}
    <div class="table-card">
        <x-admin.filter-bar
            searchId="rthSearch"
            searchPlaceholder="Cari Kelurahan..."
            searchAction="filterRthTable()"
            :filters="[
                [
                    'id'      => 'rthYear',
                    'action'  => 'filterRthTable()',
                    'options' => collect(['' => 'Semua Tahun'])
                        ->merge(collect($years)->mapWithKeys(fn($y) => [$y => $y]))
                        ->toArray(),
                ],
                [
                    'id'      => 'rthStatus',
                    'action'  => 'filterRthTable()',
                    'options' => [
                        ''                => 'Semua Status',
                        'Memenuhi'        => 'Memenuhi',
                        'Belum Memenuhi'  => 'Belum Memenuhi',
                    ],
                ],
            ]"
        />
        <div class="table-wrap">
            <div class="table-scroll">
                <table id="rthTable" class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kelurahan</th>
                            <th>Tahun</th>
                            <th class="num">Luas Kelurahan (km²)</th>
                            <th class="num">Luas RTH (km²)</th>
                            <th class="num">Jumlah Penduduk</th>
                            <th class="num">RTH per Kapita (m²/jiwa)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $row)
                            @php
                                // Warna kolom RTH per Kapita ikut status Permen PU yang
                                // sudah dihitung di AdminController (per kapita ≥0,30
                                // m²/jiwa DAN luas ≥9.000 m²) — persentase RTH sengaja
                                // tidak ditampilkan di sini karena kolom persentase_rth
                                // di database tidak konsisten skalanya (lihat catatan
                                // di DataController/PetaController), jadi status per
                                // kelurahan tidak boleh bergantung padanya sama sekali.
                                $valClass = $row['status'] === 'Memenuhi' ? 'val--ok' : 'val--danger';
                            @endphp
                            <tr
                                data-kec="{{ strtolower($row['kelurahan']) }}"
                                data-tahun="{{ $row['tahun'] }}"
                                data-status="{{ $row['status'] }}"
                            >
                                <td data-label="No">{{ $i + 1 }}</td>
                                <td data-label="Kelurahan" style="font-weight:600;">{{ $row['kelurahan'] }}</td>
                                <td data-label="Tahun">{{ $row['tahun'] }}</td>
                                <td class="num" data-label="Luas Kelurahan (km²)">{{ number_format($row['luas_kelurahan'], 4) }}</td>
                                <td class="num" data-label="Luas RTH (km²)">{{ number_format($row['luas_rth'], 4) }}</td>
                                <td class="num" data-label="Jumlah Penduduk">{{ number_format($row['jumlah_penduduk']) }}</td>
                                <td class="num {{ $valClass }}" data-label="RTH per Kapita (m²/jiwa)">{{ number_format($row['rth_per_kapita'], 2) }}</td>
                                <td data-label="Status">
                                    <x-admin.badge :status="$row['status']" />
                                </td>
                                <td data-label="Aksi">
                                    <x-admin.action-buttons
                                        edit-modal="rthModal"
                                        :edit-fields="[
                                            'id' => $row['id'],
                                            'gid' => $row['gid'],
                                            'tahun' => $row['tahun'],
                                            'luas_kelurahan' => $row['luas_kelurahan'],
                                            'luas_rth' => $row['luas_rth'],
                                        ]"
                                        delete-modal="modal-delete"
                                        :delete-url="route('admin.rth.destroy', $row['id'])"
                                        :delete-label="$row['kelurahan']"
                                    />
                                </td>
                            </tr>
                        @endforeach
                        @if(count($rows) === 0)
                            <tr class="empty-row">
                                <td colspan="9">Tidak ada data yang ditemukan.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{{-- ── RTH Modal ── --}}
<x-admin.modal id="rthModal" title="Tambah Data RTH" subtitle="Lengkapi data ruang terbuka hijau per kelurahan" icon="form">
    <div class="form-section-label">Lokasi</div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Kelurahan <span>*</span></label>
            <select name="gid" class="form-input" data-kelurahan-select onchange="syncKecamatan(this)">
                <option value="" disabled selected>Pilih kelurahan…</option>
                @foreach($kelurahan as $k)
                    <option value="{{ $k->gid }}" data-wadmkc="{{ $k->wadmkc ?? '' }}">
                        {{ $k->namobj }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Kecamatan</label>
            <input type="text" name="wadmkc" class="form-input form-computed" data-kecamatan-field readonly placeholder="Terisi otomatis">
        </div>
    </div>
    <div class="form-section-label">Luas &amp; Tahun</div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Tahun <span>*</span></label>
            @php
                $tahunOptions = collect($years)
                    ->push((int) date('Y'))
                    ->push((int) date('Y') + 1)
                    ->unique()
                    ->sortDesc()
                    ->values();
            @endphp
            <select name="tahun" class="form-input">
                @foreach($tahunOptions as $ty)
                    <option value="{{ $ty }}" {{ $ty == date('Y') ? 'selected' : '' }}>{{ $ty }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Luas Kelurahan (km²)</label>
            <input type="number" step="0.0001" name="luas_kelurahan" class="form-input" placeholder="0.0000">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Luas RTH (km²)</label>
            <input type="number" step="0.0001" name="luas_rth" class="form-input" placeholder="0.0000">
        </div>
        <div class="form-group">
            <label class="form-label">% RTH</label>
            <input type="text" name="persen_rth" class="form-input form-computed" readonly placeholder="Otomatis dihitung">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Status Kepatuhan</label>
        <input type="text" name="status_rth" class="form-input form-computed status-preview" readonly placeholder="Otomatis dihitung">
        <div class="form-hint">Dihitung otomatis dari Luas RTH ÷ Luas Kelurahan, mengacu Permen PU No. 05/PRT/M/2008.</div>
    </div>
    <x-slot:footer>
        <button type="button" class="btn btn--outline" data-modal-close="rthModal">Batal</button>
        <button type="button" class="btn btn--primary" onclick="saveRth()">Simpan</button>
    </x-slot:footer>
</x-admin.modal>
{{-- ── Delete Modal ── --}}
<x-admin.delete-modal id="modal-delete" />
@endsection
@section('extra-styles')
<style>
    /* ── Stat cards: fluid grid, wraps naturally ── */
    .admin-stats {
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
    /* ── Table wrapper: horizontal scroll on tablets ── */
    .table-card {
        width: 100%;
        overflow: hidden;
    }
    .table-wrap,
    .table-scroll {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .data-table {
        width: 100%;
        min-width: 820px;
        border-collapse: collapse;
    }
    .data-table th.num,
    .data-table td.num {
        text-align: right;
        white-space: nowrap;
    }
    /* ── Mobile: convert table rows into stacked cards ── */
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
            white-space: normal;
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
// ── CSRF helper ─────────────────────────────────
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}
// ── Toast helper ─────────────────────────────────
function showToast(msg, isError = false) {
    // Implementasi toast - sesuaikan dengan sistem toast yang ada
    alert(msg);
}
// ── Table filter ─────────────────────────────────
function filterRthTable() {
    const q  = (document.getElementById('rthSearch')?.value ?? '').toLowerCase().trim();
    const yr = document.getElementById('rthYear')?.value ?? '';
    const st = document.getElementById('rthStatus')?.value ?? '';
    document.querySelectorAll('#rthTable tbody tr:not(.empty-row)').forEach(tr => {
        const kec    = tr.dataset.kec ?? '';
        const tahun  = String(tr.dataset.tahun ?? '');
        const status = tr.dataset.status ?? '';
        const show = (!q  || kec.includes(q))
                  && (!yr || tahun === yr)
                  && (!st || status === st);
        tr.style.display = show ? '' : 'none';
    });
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
// ── Auto-hitung % RTH & status ────────────────────
function hitungRth() {
    const modal   = document.getElementById('rthModal');
    if (!modal) return;
    const wilayah = parseFloat(modal.querySelector('[name="luas_kelurahan"]')?.value) || 0;
    const rth     = parseFloat(modal.querySelector('[name="luas_rth"]')?.value) || 0;
    const pField  = modal.querySelector('[name="persen_rth"]');
    const sField  = modal.querySelector('[name="status_rth"]');
    if (wilayah > 0 && pField && sField) {
        const persen = (rth / wilayah) * 100;
        pField.value = persen.toFixed(4) + '%';
        // Status resmi (Permen PU: per kapita ≥0,30 m²/jiwa DAN luas ≥9.000 m²)
        // butuh data jumlah penduduk kelurahan ini, yang tidak diinput di form
        // ini — jadi status final dihitung di server (AdminController) begitu
        // data disimpan, bukan ditebak di sini cuma dari %.
        sField.value = 'Dihitung otomatis setelah disimpan';
        sField.classList.remove('is-ok', 'is-danger');
    } else if (pField && sField) {
        pField.value = '';
        sField.value = '';
        sField.classList.remove('is-ok', 'is-danger');
    }
}
// Pasang listener ke modal RTH saja (bukan document)
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('rthModal');
    if (modal) {
        modal.addEventListener('input', hitungRth);
    }
});
// ── Buka modal edit ────────────────────────────────
function openEditModal(modalId, data) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    // Update judul
    const titleEl = document.getElementById(modalId + 'Title');
    if (titleEl) titleEl.textContent = data.id ? 'Edit Data RTH' : 'Tambah Data RTH';
    // Isi field
    const set = (name, val) => {
        const el = modal.querySelector(`[name="${name}"]`);
        if (!el) return;
        el.value = val ?? '';
        // Selects are backed by a custom dropdown UI that listens for
        // 'change' to keep its display in sync with the real <select>.
        if (el.tagName === 'SELECT') {
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };
    set('id',              data.id ?? '');
    set('gid',             data.gid ?? '');
    set('tahun',           data.tahun ?? new Date().getFullYear());
    set('luas_kelurahan', data.luas_kelurahan ?? '');
    set('luas_rth',        data.luas_rth ?? '');
    // Kecamatan mengikuti kelurahan yang baru saja di-set
    const gidSelect = modal.querySelector('[data-kelurahan-select]');
    if (gidSelect) syncKecamatan(gidSelect);
    // Hitung otomatis
    hitungRth();
    modal.classList.add('open');
}
// ── Reset modal saat ditutup ────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modalClose);
            if (modal) resetModal(modal);
        });
    });
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) resetModal(overlay);
        });
    });
    // Reset juga saat modal ditutup pakai tombol Escape, supaya data
    // lama (termasuk hidden id) tidak "nyangkut" ke sesi berikutnya.
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('rthModal');
            if (modal) resetModal(modal);
        }
    });
});
function resetModal(modal) {
    modal.querySelectorAll('input').forEach(el => {
        el.value = '';
    });
    modal.querySelectorAll('select').forEach(el => {
        el.selectedIndex = 0;
        el.dispatchEvent(new Event('change', { bubbles: true }));
    });
    modal.querySelectorAll('.status-preview').forEach(el => {
        el.classList.remove('is-ok', 'is-danger');
    });
    const titleEl = modal.querySelector('.modal__title');
    // Kembalikan judul default
    if (titleEl && modal.id === 'rthModal') titleEl.textContent = 'Tambah Data RTH';
}
// ── Simpan RTH ─────────────────────────────────────
async function saveRth() {
    const modal = document.getElementById('rthModal');
    const get   = name => modal.querySelector(`[name="${name}"]`)?.value ?? '';
    const id      = get('id');
    const payload = {
        gid: get('gid'),
        tahun: get('tahun'),
        luas_kelurahan: get('luas_kelurahan'),
        luas_rth: get('luas_rth'),
    };
    // Validasi sederhana
    if (!payload.gid || !payload.tahun || !payload.luas_kelurahan || !payload.luas_rth) {
        showToast('Semua field wajib diisi.', true);
        return;
    }
    const url    = id ? `/admin/rth/${id}` : '/admin/rth';
    const method = id ? 'PUT' : 'POST';
    try {
        const res  = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message ?? 'Data berhasil disimpan.');
            modal.classList.remove('open');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message ?? 'Gagal menyimpan data.', true);
        }
    } catch (err) {
        showToast('Terjadi kesalahan jaringan.', true);
        console.error(err);
    }
}
// ── Konfirmasi & hapus ────────────────────────────
function confirmDelete(modalId, url, label) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    // Set label di modal konfirmasi jika ada elemen-nya
    const labelEl = modal.querySelector('[data-delete-label]');
    if (labelEl) labelEl.textContent = label;
    // Simpan URL ke tombol konfirmasi
    const confirmBtn = modal.querySelector('[data-delete-confirm]');
    if (confirmBtn) {
        confirmBtn.onclick = () => doDelete(url, modalId);
    }
    modal.classList.add('open');
}
async function doDelete(url, modalId) {
    try {
        const res  = await fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        });
        const data = await res.json();
        document.getElementById(modalId)?.classList.remove('open');
        if (data.success) {
            showToast(data.message ?? 'Data berhasil dihapus.');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message ?? 'Gagal menghapus data.', true);
        }
    } catch (err) {
        showToast('Terjadi kesalahan jaringan.', true);
        console.error(err);
    }
}
</script>
@endsection