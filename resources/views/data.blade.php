@extends('layouts.app')
@section('title', 'Dashboard Analisis RTH — Kota Bekasi')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════
   RESET & BASE
═══════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

svg { overflow: visible; }
svg path, svg rect, svg circle, svg ellipse,
svg polygon, svg polyline, svg line {
  vector-effect: non-scaling-stroke;
}

:root {
  --bg:         #f4f3f0;
  --surface:    #ffffff;
  --surface2:   #f9f8f6;
  --surface3:   #f1f0ed;
  --border:     rgba(0,0,0,0.08);
  --border2:    rgba(0,0,0,0.05);
  --ink1:       #1a1917;
  --ink2:       #5a5855;
  --ink3:       #9a9895;

  --red:        #c0392b;
  --red-bg:     #fdf0ef;
  --red-bd:     rgba(192,57,43,0.18);
  --red-dk:     #922b21;

  --amber:      #b45309;
  --amber-bg:   #fefbf0;

  --green:      #15803d;
  --green-bg:   #f0fdf4;

  --blue:       #1d4ed8;
  --blue-bg:    #eff6ff;

  --r:         10px;
  --r-lg:     14px;
  --r-xl:     18px;
  --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
  --shadow-md: 0 4px 12px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.04);

  --sidebar-w: 230px;
}

html { scroll-behavior: smooth; }

body {
  background: var(--bg);
  font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
  color: var(--ink1);
  font-size: 13px;
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}

/* ═══════════════════════════════════════
   LAYOUT UTAMA
═══════════════════════════════════════ */
.main-col {
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
}

/* ═══════════════════════════════════════
   TOPBAR
═══════════════════════════════════════ */
.topbar {
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  padding: 0 24px;
  height: 54px;
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  position: sticky; top: 0; z-index: 200;
  flex-shrink: 0;
}

.tb-left  { display: flex; align-items: center; gap: 10px; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 600; letter-spacing: -0.3px; white-space: nowrap; }
.tb-sep   { color: var(--border); font-weight: 300; font-size: 16px; flex-shrink: 0; }
.tb-desc  { font-size: 12px; color: var(--ink3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tb-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.btn-reset {
  display: flex; align-items: center; gap: 6px;
  font-size: 12.5px; font-weight: 500;
  padding: 6px 12px;
  border: 1px solid var(--border); border-radius: 8px;
  background: var(--surface); color: var(--ink2);
  cursor: pointer; transition: all 0.15s; font-family: inherit;
}
.btn-reset svg {
  width: 13px; height: 13px;
  fill: none !important; stroke-width: 2;
  stroke-linecap: round; stroke-linejoin: round;
}
.btn-reset:hover { background: var(--bg); border-color: var(--ink3); color: var(--ink1); }

.yr-sel {
  font-size: 12.5px; font-weight: 500;
  padding: 6px 28px 6px 10px;
  border: 1px solid var(--border); border-radius: 8px;
  background: var(--surface)
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239a9895' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E")
    no-repeat right 8px center;
  color: var(--ink1); cursor: pointer; outline: none;
  -webkit-appearance: none; appearance: none; font-family: inherit;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.yr-sel:hover  { border-color: var(--ink3); }
.yr-sel:focus  { border-color: var(--ink2); box-shadow: 0 0 0 2px rgba(0,0,0,0.06); }

/* ═══════════════════════════════════════
   CONTENT AREA
═══════════════════════════════════════ */
.content {
  padding: 20px 24px 32px;
  display: flex; flex-direction: column; gap: 14px;
  flex: 1;
  min-width: 0;
}

/* ═══════════════════════════════════════
   ALERT BANNER
═══════════════════════════════════════ */
.alert-banner {
  background: var(--red-bg);
  border: 1px solid var(--red-bd);
  border-radius: var(--r);
  padding: 10px 14px;
  display: flex; align-items: flex-start; gap: 9px;
  font-size: 12.5px; color: var(--red-dk);
  line-height: 1.55;
}
.alert-banner svg {
  width: 15px; height: 15px;
  flex-shrink: 0; margin-top: 1px;
  fill: none !important; stroke: var(--red);
  stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
}

/* ═══════════════════════════════════════
   STAT CARDS
═══════════════════════════════════════ */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 10px;
}

.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 14px 14px 12px;
  box-shadow: var(--shadow);
  transition: box-shadow 0.2s;
}
.stat-card:hover { box-shadow: var(--shadow-md); }

.stat-ic {
  width: 30px; height: 30px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 11px;
}
.stat-ic svg {
  width: 13px; height: 13px;
  fill: none !important; stroke-width: 1.8;
  stroke-linecap: round; stroke-linejoin: round;
}
.stat-ic svg * { fill: none !important; }

.ic-n { background: #f4f4f2; } .ic-n svg { stroke: #888; }
.ic-g { background: var(--green-bg); } .ic-g svg { stroke: var(--green); }
.ic-a { background: var(--amber-bg); } .ic-a svg { stroke: var(--amber); }
.ic-r { background: var(--red-bg); }   .ic-r svg { stroke: var(--red); }
.ic-b { background: var(--blue-bg); }  .ic-b svg { stroke: var(--blue); }

.stat-lbl {
  font-size: 10px; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.6px;
  color: var(--ink3); margin-bottom: 4px;
}
.stat-val {
  font-size: 20px; font-weight: 600;
  letter-spacing: -0.8px; line-height: 1.1;
  font-family: 'DM Mono', monospace;
}
.stat-val.c-r { color: var(--red); }
.stat-val.c-a { color: var(--amber); }
.stat-val.c-g { color: var(--green); }
.stat-sub { font-size: 10.5px; color: var(--ink3); margin-top: 5px; }

/* ═══════════════════════════════════════
   CARD UMUM
═══════════════════════════════════════ */
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  box-shadow: var(--shadow);
  overflow: hidden;
  display: flex; flex-direction: column;
}

.card-head {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border2);
  display: flex; align-items: flex-start;
  justify-content: space-between; gap: 8px;
  flex-shrink: 0;
}
.card-head-text h3 { font-size: 13px; font-weight: 600; letter-spacing: -0.2px; color: var(--ink1); }
.card-head-text p  { font-size: 11px; color: var(--ink3); margin-top: 2px; }

.card-body { padding: 14px 16px; flex: 1; min-width: 0; }
.card-body.pad-sm { padding: 10px 12px; }

/* ═══════════════════════════════════════
   GRID HELPERS
═══════════════════════════════════════ */
.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  min-width: 0;
}

.grid-map {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
  align-items: stretch;
}

/* ═══════════════════════════════════════
   PETA LEAFLET
═══════════════════════════════════════ */
.peta-el {
  height: 390px;
  border-radius: 8px;
  overflow: hidden;
  z-index: 1;
}

.map-legend {
  padding: 10px 16px;
  border-top: 1px solid var(--border2);
  display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
  flex-shrink: 0;
}
.legend-title {
  font-size: 10.5px; font-weight: 600;
  color: var(--ink2); width: 100%; margin-bottom: 2px;
}
.leg-row {
  display: flex; align-items: center; gap: 6px;
  font-size: 11.5px; color: var(--ink2);
}
.leg-swatch {
  width: 11px; height: 11px;
  border-radius: 3px; flex-shrink: 0;
}

/* ═══════════════════════════════════════
   LABEL NAMA KELURAHAN DI PETA
═══════════════════════════════════════ */
.kel-label {
  background: transparent;
  border: none;
  box-shadow: none;
  font-family: 'DM Sans', sans-serif;
  font-size: 10.5px;
  font-weight: 700;
  color: #1a1917;
  text-shadow:
    -1px -1px 0 #fff, 1px -1px 0 #fff,
    -1px  1px 0 #fff, 1px  1px 0 #fff,
     0px  1.5px 0 #fff, 0px -1.5px 0 #fff,
     1.5px 0px 0 #fff, -1.5px 0px 0 #fff;
  white-space: nowrap;
  pointer-events: none;
  text-align: center;
}
.leaflet-tooltip.kel-label::before { display: none; }

.rth-hover-tip {
  background: rgba(255,255,255,0.98);
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 10px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.12);
  padding: 0;
  opacity: 1 !important;
}
.rth-hover-tip::before { display: none; }

/* ═══════════════════════════════════════
   BADGE
═══════════════════════════════════════ */
.badge {
  display: inline-flex; align-items: center;
  padding: 2px 8px; border-radius: 20px;
  font-size: 10.5px; font-weight: 600;
  font-family: 'DM Mono', monospace;
  white-space: nowrap;
}
.bdg-aman   { background: var(--green-bg); color: var(--green); }
.bdg-kritis { background: var(--red-bg);   color: var(--red); }

.bdg-prioritas-tinggi { background: var(--red-bg);   color: var(--red); }
.bdg-prioritas-sedang { background: var(--amber-bg); color: var(--amber); }
.bdg-prioritas-rendah { background: var(--green-bg); color: var(--green); }

.bdg-naik  { background: var(--green-bg); color: var(--green); }
.bdg-turun { background: var(--red-bg);   color: var(--red); }
.bdg-tetap { background: var(--surface3); color: var(--ink2); }

/* ═══════════════════════════════════════
   PERUBAHAN LUAS RTH
═══════════════════════════════════════ */
.change-body {
  display: flex; align-items: center; justify-content: flex-start;
  gap: 16px; flex-wrap: wrap;
}
.change-counts { display: flex; gap: 8px; flex-wrap: wrap; }
.change-count-item {
  font-size: 11.5px; font-weight: 600;
  padding: 5px 11px; border-radius: 20px;
  white-space: nowrap;
}
.cc-g { background: var(--green-bg); color: var(--green); }
.cc-r { background: var(--red-bg);   color: var(--red); }
.cc-n { background: var(--surface3); color: var(--ink2); }

/* ═══════════════════════════════════════
   CHART LEGEND
═══════════════════════════════════════ */
.chart-leg {
  display: flex; flex-wrap: wrap; gap: 10px;
  margin-bottom: 10px;
  font-size: 11.5px; color: var(--ink2);
}
.chart-leg-item { display: flex; align-items: center; gap: 4px; }
.chart-leg-dot  { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }

.cw { position: relative; width: 100%; }

/* ═══════════════════════════════════════
   TABEL ANALISIS PER KELURAHAN
═══════════════════════════════════════ */
.table-toolbar {
  display: flex; align-items: center; justify-content: space-between;
  gap: 10px;
  padding: 10px 16px;
  border-bottom: 1px solid var(--border2);
  flex-wrap: wrap;
}
.table-search {
  position: relative;
  flex: 1 1 260px;
  max-width: 320px;
}
.table-search svg {
  position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
  width: 14px; height: 14px;
  fill: none !important; stroke: var(--ink3);
  stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
  pointer-events: none;
}
.table-search input {
  width: 100%;
  font-family: inherit;
  font-size: 12.5px;
  padding: 7px 10px 7px 30px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--surface2);
  color: var(--ink1);
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}
.table-search input::placeholder { color: var(--ink3); }
.table-search input:focus {
  background: var(--surface);
  border-color: var(--ink3);
  box-shadow: 0 0 0 2px rgba(0,0,0,0.06);
}
.table-search .clear-search {
  position: absolute; right: 7px; top: 50%; transform: translateY(-50%);
  width: 18px; height: 18px;
  border: none; background: transparent;
  color: var(--ink3); cursor: pointer;
  display: none;
  align-items: center; justify-content: center;
  border-radius: 50%;
  font-size: 13px; line-height: 1;
}
.table-search .clear-search:hover { background: var(--bg); color: var(--ink1); }
.table-search.has-value .clear-search { display: flex; }
.table-count {
  font-size: 11.5px; color: var(--ink3);
  white-space: nowrap; flex-shrink: 0;
}
.table-count b { color: var(--ink1); font-weight: 600; }

.table-wrap {
  overflow: auto;
  max-height: 520px;
}

.data-table {
  width: 100%;
  min-width: 1080px;
  border-collapse: collapse;
}
.data-table th,
.data-table td {
  padding: 10px 14px;
  text-align: left;
  white-space: nowrap;
  border-bottom: 1px solid var(--border2);
}
.data-table thead th {
  position: sticky;
  top: 0;
  z-index: 3;
  background: var(--surface2);
  font-size: 10.5px; font-weight: 600;
  color: var(--ink2);
  letter-spacing: 0.1px;
  user-select: none;
}
.data-table .th-sub {
  display: block;
  font-size: 9.5px; font-weight: 500;
  color: var(--ink3);
  margin-top: 1px;
}
.data-table th.sortable { cursor: pointer; transition: color 0.15s; }
.data-table th.sortable:hover  { color: var(--ink1); }
.data-table th.sortable.sorted { color: var(--ink1); }
.data-table .sort-ic {
  font-size: 10px; color: var(--ink3); margin-left: 3px;
}
.data-table th.sortable.sorted .sort-ic { color: var(--ink1); }

.data-table tbody tr:hover { background: var(--surface2); }
.data-table td { font-size: 12px; color: var(--ink2); }
.data-table td.td-name { color: var(--ink1); font-weight: 500; }

.data-table mark {
  background: #fef08a;
  color: inherit;
  border-radius: 2px;
  padding: 0 1px;
}

.td-kepadatan-val {
  font-weight: 600; color: var(--ink1);
  font-family: 'DM Mono', monospace;
}
.td-kepadatan-sub { font-size: 10px; color: var(--ink3); margin-top: 1px; }

.data-table th:nth-child(1), .data-table td:nth-child(1) {
  position: sticky; left: 0; z-index: 2;
  background: var(--surface);
  width: 42px; text-align: center;
}
.data-table th:nth-child(2), .data-table td:nth-child(2) {
  position: sticky; left: 42px; z-index: 2;
  background: var(--surface);
  box-shadow: 2px 0 4px rgba(0,0,0,0.035);
}
.data-table thead th:nth-child(1),
.data-table thead th:nth-child(2) {
  background: var(--surface2);
  z-index: 4;
}

/* ═══════════════════════════════════════
   POPUP LEAFLET
═══════════════════════════════════════ */
.rth-popup {
  min-width: 200px;
  font-family: 'DM Sans', sans-serif;
  font-size: 12.5px;
}
.rth-popup .pop-name {
  font-weight: 700; font-size: 14px;
  margin-bottom: 3px; color: var(--ink1);
}
.rth-popup .pop-kec {
  font-size: 11px; color: var(--ink3);
  margin-bottom: 9px;
  display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.rth-popup table {
  width: 100%; border-collapse: collapse;
  margin-bottom: 8px;
}
.rth-popup td {
  padding: 3px 0;
  color: var(--ink2);
  font-size: 11.5px;
  border-bottom: 1px solid var(--border2);
}
.rth-popup tr:last-child td { border-bottom: none; }
.rth-popup td:last-child {
  text-align: right; font-weight: 600;
  color: var(--ink1);
  font-family: 'DM Mono', monospace;
}

/* ═══════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════ */
@media (max-width: 1200px) {
  .stats-grid { grid-template-columns: repeat(3, 1fr); }
  .grid-map   { grid-template-columns: 1fr; }
}

@media (max-width: 780px) {
  .grid-2     { grid-template-columns: 1fr; }
}

@media (max-width: 540px) {
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .content { padding: 12px 12px 20px; }
  .card-head { flex-wrap: wrap; }
  .data-table th, .data-table td { padding: 8px 10px; font-size: 11.5px; }
  .table-wrap { max-height: 420px; }
  .table-toolbar { flex-direction: column; align-items: stretch; }
  .table-search { max-width: none; }
}
</style>
@endpush

<style>
.rth-lp .leaflet-popup-content-wrapper {
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.12), 0 1px 4px rgba(0,0,0,0.06);
  padding: 0; overflow: hidden;
  border: 1px solid rgba(0,0,0,0.07);
}
.rth-lp .leaflet-popup-content { margin: 13px 15px; }
.rth-lp .leaflet-popup-tip { background: #fff; }
</style>

@section('content')
<div class="dash">

  <div class="main-col">

    {{-- ── Topbar ── --}}
    <header class="topbar">
      <div class="tb-left">
        <span class="tb-title" id="section-title">Ringkasan</span>
        <span class="tb-sep">/</span>
        <span class="tb-desc" id="section-desc">Gambaran umum RTH dan kependudukan kota</span>
      </div>
      <div class="tb-right">
        <button
          type="button"
          class="btn-reset"
          onclick="clearFilter()"
          title="Reset semua filter">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="1 4 1 10 7 10"/>
            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
          </svg>
          Reset
        </button>
        <select
          id="year"
          name="year"
          class="yr-sel"
          onchange="changeYear(this.value)"
          aria-label="Pilih tahun data">
          @foreach($availableYears as $y)
            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
              Tahun {{ $y }}
            </option>
          @endforeach
        </select>
      </div>
    </header>

    {{-- ── Content ── --}}
    <div class="content">

      {{-- ── Ringkasan Perubahan Luas RTH ── --}}
      @if($stats['tahun_pembanding'])
        <div class="card change-card {{ $stats['diff_persen_rth_kota'] < 0 ? 'is-turun' : '' }}" id="changeSummaryCard">
          <div class="card-head">
            <div class="card-head-text">
              <h3>Perubahan Luas RTH: {{ $stats['tahun_pembanding'] }} → {{ $selectedYear }}</h3>
              <p>Analisis perubahan luas RTH kota dibanding tahun sebelumnya, per kelurahan.</p>
            </div>
          </div>
          <div class="card-body change-body">
            <div class="change-counts">
              <div class="change-count-item cc-g">▲ <span id="changeNaik">{{ $stats['kelurahan_naik'] }}</span> kelurahan naik</div>
              <div class="change-count-item cc-r">▼ <span id="changeTurun">{{ $stats['kelurahan_turun'] }}</span> kelurahan turun</div>
              <div class="change-count-item cc-n">▬ <span id="changeTetap">{{ $stats['kelurahan_tetap'] }}</span> kelurahan tetap</div>
            </div>
          </div>
        </div>
      @else
        <div class="alert-banner" role="status" style="background:var(--surface2);border-color:var(--border);color:var(--ink2);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
          </svg>
          <div>
            <strong>{{ $selectedYear }}</strong> adalah tahun data paling awal yang tersedia, jadi belum ada tahun pembanding untuk analisis perubahan luas RTH.
          </div>
        </div>
      @endif

      @php
        $totalPenduduk = $stats['total_penduduk'];
        $rthPerKapita  = $stats['rth_per_kapita'];
      @endphp

      {{-- ── Stat Cards ── --}}
      <div class="stats-grid">

        {{-- Kelurahan --}}
        <div class="stat-card">
          <div class="stat-ic ic-n">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </div>
          <div class="stat-lbl">Kelurahan</div>
          <div class="stat-val" id="cardKelurahan">{{ $stats['total_kelurahan'] }}</div>
          <div class="stat-sub">total wilayah</div>
        </div>

        {{-- Penduduk --}}
        <div class="stat-card">
          <div class="stat-ic ic-b">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
          </div>
          <div class="stat-lbl">Total Penduduk</div>
          <div class="stat-val" id="cardPenduduk">{{ number_format($stats['total_penduduk'],0,',','.') }}</div>
          <div class="stat-sub">jiwa, {{ $selectedYear }}</div>
        </div>

        {{-- Kepadatan --}}
        <div class="stat-card">
          <div class="stat-ic ic-a">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
          </div>
          <div class="stat-lbl">Kepadatan Kota</div>
          <div class="stat-val c-a" id="cardKepadatan">{{ number_format($stats['kepadatan_kota'],0,',','.') }}</div>
          <div class="stat-sub">jiwa/km²</div>
        </div>

        {{-- % RTH --}}
        <div class="stat-card">
          <div class="stat-ic ic-r">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <div class="stat-lbl">% Ruang Terbuka Hijau (RTH)</div>
          <div class="stat-val {{ $stats['persen_rth']>=20?'c-g':'c-r' }}" id="cardRth">
            {{ $stats['persen_rth'] }}%
          </div>
          <div class="stat-sub">standar ≥ 20%</div>
        </div>

        {{-- RTH per Kapita --}}
        <div class="stat-card">
          <div class="stat-ic ic-r">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a7 7 0 017 7c0 5-7 13-7 13S5 14 5 9a7 7 0 017-7z"/>
              <circle cx="12" cy="9" r="2.5"/>
            </svg>
          </div>
          <div class="stat-lbl">RTH per Kapita</div>
          <div class="stat-val {{ $stats['rth_per_kapita'] >= 9 ? 'c-g' : 'c-r' }}" id="cardKapita">
            {{ $stats['rth_per_kapita'] }} m²
          </div>
          <div class="stat-sub">WHO ≥ 9 m²/jiwa</div>
        </div>

        {{-- Belum Memenuhi --}}
        <div class="stat-card">
          <div class="stat-ic ic-r">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <div class="stat-lbl">Kelurahan Belum Memenuhi</div>
          <div class="stat-val c-r" id="cardBelum">
            {{ $stats['kelurahan_kritis'] }}
          </div>
          <div class="stat-sub" id="cardDefisit">
            defisit {{ number_format($stats['total_defisit'],1,',','.') }} km²
          </div>
        </div>

      </div>

      {{-- ── Peta RTH & Peta Kepadatan ── --}}
      <div class="grid-map">

        {{-- Peta RTH --}}
        <div class="card">
          <div class="card-head">
            <div class="card-head-text">
              <h3>Peta RTH per Kelurahan (Permen PU No. 05/PRT/M/2008)</h3>
              <p>Klasifikasi: min. 0,30 m² RTH/jiwa &amp; luas taman min. 9.000 m². Klik poligon untuk mengunci filter.</p>
            </div>
          </div>
          <div class="card-body pad-sm" style="padding-bottom:0">
            <div id="rth-map-rth" class="peta-el"></div>
          </div>
          <div class="map-legend">
            <div class="legend-title">Standar Permen PU No. 05/PRT/M/2008</div>
            <div style="display:flex;flex-wrap:wrap;gap:10px">
              <div class="leg-row"><span class="leg-swatch" style="background:#2E7D32"></span>Memenuhi (≥0,30 m²/jiwa &amp; ≥9.000 m² luas taman)</div>
              <div class="leg-row"><span class="leg-swatch" style="background:#D32F2F"></span>Belum Memenuhi</div>
            </div>
          </div>
        </div>

        {{-- Peta Kepadatan Penduduk --}}
        <div class="card">
          <div class="card-head">
            <div class="card-head-text">
              <h3>Peta Kepadatan Penduduk per Kelurahan</h3>
              <p>Distribusi kepadatan penduduk antar kelurahan. Klik poligon untuk mengunci filter.</p>
            </div>
          </div>
          <div class="card-body pad-sm" style="padding-bottom:0">
            <div id="rth-map-kepadatan" class="peta-el"></div>
          </div>
          <div class="map-legend">
            <div class="legend-title">Kepadatan Penduduk (SNI 03-1733-2004)</div>
            <div style="display:flex;flex-wrap:wrap;gap:10px">
              <div class="leg-row"><span class="leg-swatch" style="background:#8B2C24"></span>Sangat Padat &gt; 400 jiwa/ha</div>
              <div class="leg-row"><span class="leg-swatch" style="background:#D43C33"></span>Tinggi 201-400 jiwa/ha</div>
              <div class="leg-row"><span class="leg-swatch" style="background:#E67E22"></span>Sedang 151-200 jiwa/ha</div>
              <div class="leg-row"><span class="leg-swatch" style="background:#7CAE7A"></span>Rendah &lt; 150 jiwa/ha</div>
            </div>
          </div>
        </div>

      </div>

      {{-- ── % RTH 15 Terendah & Top 15 Kepadatan ── --}}
      <div class="grid-2">

        <div class="card">
          <div class="card-head">
            <div class="card-head-text">
              <h3>15 Kelurahan dengan Persentase RTH Terendah</h3>
              <p>Kandidat prioritas penghijauan</p>
            </div>
          </div>
          <div class="card-body">
            <div class="cw" style="height:{{ max(count($rthBarData), 5) * 32 + 24 }}px">
              <canvas id="rthBarChart"></canvas>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-head">
            <div class="card-head-text">
              <h3>15 Kelurahan dengan Jumlah Penduduk Tertinggi</h3>
              <p>Tekanan penduduk tertinggi</p>
            </div>
          </div>
          <div class="card-body">
            <div class="cw" style="height:{{ max(count($popData), 5) * 32 + 24 }}px">
              <canvas id="popChart"></canvas>
            </div>
          </div>
        </div>

      </div>

      {{-- ── Analisis Perubahan ── --}}
      <div class="card">
        <div class="card-head">
          <div class="card-head-text">
            <h3>15 Kelurahan dengan Perubahan RTH Terbesar</h3>
            <p>
              @if($stats['tahun_pembanding'])
                Dibanding tahun {{ $stats['tahun_pembanding'] }}, diurutkan dari penurunan RTH terbesar ke kenaikan RTH terbesar
              @else
                Analisis perubahan luas RTH per kelurahan antar tahun
              @endif
            </p>
          </div>
        </div>
        <div class="card-body">
          @if($stats['tahun_pembanding'])
            <div class="cw" style="height:{{ max(count($rthChangeData), 5) * 32 + 24 }}px">
              <canvas id="rthChangeChart"></canvas>
            </div>
          @else
            <div style="text-align:center;color:var(--ink3);padding:24px;font-size:12.5px;">
              Tidak ada data pembanding untuk tahun {{ $selectedYear }} (tahun paling awal pada data).
            </div>
          @endif
        </div>
      </div>

      {{-- ── Tren ── --}}
      <div class="card">
        <div class="card-head">
          <div class="card-head-text">
            <h3>
              Tren RTH &amp; Jumlah Penduduk ({{ $availableYears->first() }}–{{ $availableYears->last() }})
            </h3>
            <p>Persentase RTH dan Jumlah Penduduk Kota Bekasi Tahun {{ $availableYears->first() }}–{{ $availableYears->last() }}</p>
          </div>
        </div>
        <div class="card-body">
          <div class="chart-leg">
            <span class="chart-leg-item">
              <span class="chart-leg-dot" style="background:#15803d"></span>
              % RTH rata-rata kota (sumbu kiri)
            </span>
            <span class="chart-leg-item">
              <span class="chart-leg-dot" style="background:#c0392b"></span>
              Jumlah penduduk (jiwa) (sumbu kanan)
            </span>
          </div>
          <div class="cw" style="height:220px">
            <canvas id="trendChart"></canvas>
          </div>
        </div>
      </div>

      {{-- ── Tabel Analisis Per Kelurahan ── --}}
      <div class="card">

        <div class="card-head">
          <div class="card-head-text">
            <h3>Tabel Analisis Per Kelurahan</h3>
            <p>Klik header untuk sortir. Skor prioritas mengkombinasikan defisit RTH, kepadatan, dan RTH per kapita. Status RTH mengacu pada Permen PU No. 05/PRT/M/2008 (min. 0,30 m²/jiwa &amp; luas taman min. 9.000 m²).</p>
          </div>
        </div>

        <div class="table-toolbar">
          <div class="table-search" id="tableSearchWrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
              type="text"
              id="tableSearchInput"
              placeholder="Cari nama kelurahan…"
              autocomplete="off"
              oninput="onTableSearch(this.value)">
            <button type="button" class="clear-search" onclick="clearTableSearch()" title="Hapus pencarian">✕</button>
          </div>
          <div class="table-count" id="tableCount"></div>
        </div>

        <div class="card-body" style="padding:0">
          <div class="table-wrap">
            <table class="data-table" id="analysisTable">
              <thead>
                <tr>
                  <th>No</th>
                  <th class="sortable" data-key="nama" onclick="sortAnalysisTable('nama')">
                    Kelurahan <span class="sort-ic">↕</span>
                  </th>
                  <th>Luas (km²)</th>
                  <th>RTH (km²)</th>
                  <th class="sortable" data-key="persen_rth" onclick="sortAnalysisTable('persen_rth')">
                    % RTH <span class="sort-ic">↕</span>
                  </th>
                  <th class="sortable" data-key="diff_persen_rth" onclick="sortAnalysisTable('diff_persen_rth')">
                    Perubahan RTH <span class="sort-ic">↕</span>
                    <span class="th-sub">vs {{ $stats['tahun_pembanding'] ?? '–' }}</span>
                  </th>
                  <th class="sortable" data-key="jumlah_penduduk" onclick="sortAnalysisTable('jumlah_penduduk')">
                    Jumlah Penduduk <span class="sort-ic">↕</span>
                    <span class="th-sub">(jiwa)</span>
                  </th>
                  <th>Kepadatan <span class="th-sub">(jiwa/ha)</span></th>
                  <th>RTH/kapita <span class="th-sub">(m²/jiwa)</span></th>
                  <th>Defisit <span class="th-sub">(m²/jiwa)</span></th>
                  <th>Status RTH <span class="th-sub">(Permen PU 05/2008)</span></th>
                  <th class="sortable" data-key="skor" onclick="sortAnalysisTable('skor')">
                    Prioritas <span class="sort-ic">↕</span>
                  </th>
                </tr>
              </thead>
              <tbody id="analysisTableBody">
                <tr><td colspan="12" style="text-align:center;color:var(--ink3);padding:20px">Memuat data…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>

/* ════════════════════════════════════════
   DATA
════════════════════════════════════════ */

const rthBarData    = @json($rthBarData);
const popData       = @json($popData);
const rthChangeData = @json($rthChangeData);
const tahunPembanding = @json($stats['tahun_pembanding']);
const trendData     = @json($trendData);
const masterData    = @json($masterData);

const mapGeoJson = @json(
    $mapGeoJson ?? [
        'type' => 'FeatureCollection',
        'features' => []
    ]
);

const cityStats = {
    persenRth: Number(@json($stats['persen_rth'])),
    kepadatanKota: Number(@json($stats['kepadatan_kota'])),
    totalPenduduk: Number(@json($stats['total_penduduk']))
};

/* =======================================
        STANDAR PERMEN PU NO. 05/PRT/M/2008
======================================= */
const PERMEN_MIN_RTH_PER_KAPITA_M2 = 0.30;
const PERMEN_MIN_LUAS_TAMAN_M2     = 9000;

function isKelurahanMemenuhi(luasRthM2, jumlahPenduduk){
    const rthPerKapita = jumlahPenduduk > 0 ? luasRthM2 / jumlahPenduduk : 0;
    return rthPerKapita >= PERMEN_MIN_RTH_PER_KAPITA_M2 &&
           luasRthM2 >= PERMEN_MIN_LUAS_TAMAN_M2;
}

// Kepadatan maksimum di seluruh kelurahan kota, dipakai untuk menormalisasi
// komponen kepadatan pada skor prioritas (lihat computeSkorPrioritas). Dihitung
// sekali dari mapGeoJson supaya sama untuk semua kelurahan, baik saat dipakai
// di tabel analisis maupun popup/tooltip peta.
const maxKepadatanGlobal = Math.max(
    ...(mapGeoJson.features || []).map(f => Number(f.properties?.kepadatan) || 0),
    1
);

// Properti GeoJSON per nama kelurahan, dipakai sebagai fallback ketika field
// yang dibutuhkan (kepadatan/luas_rth/jumlah_penduduk) tidak ada langsung di
// baris masterData — sama seperti geoByName di buildAnalysisTableData.
const geoByNameGlobal = {};
(mapGeoJson.features || []).forEach(f=>{
    const p = f.properties || {};
    if(p.nama){
        geoByNameGlobal[p.nama] = p;
    }
});

// Skor Prioritas RTH — satu-satunya sumber formula, dipakai baik oleh tabel
// analisis maupun popup/tooltip peta supaya kedua tampilan selalu sinkron.
// Sama seperti computeRekomendasi() di peta.blade: defisit RTH per kapita
// (50%) + kepadatan ternormalisasi (30%) + defisit luas minimum 9.000 m² (20%).
function computeSkorPrioritas(kepadatan, luasRthM2, jumlahPenduduk, maxKepadatan = maxKepadatanGlobal){
    const rthPerKapita = jumlahPenduduk > 0 ? luasRthM2 / jumlahPenduduk : 0;
    const defisitKapita = Math.max(0, PERMEN_MIN_RTH_PER_KAPITA_M2 - rthPerKapita);
    const skorDefisitKapita = Math.min(100, (defisitKapita / PERMEN_MIN_RTH_PER_KAPITA_M2) * 100);

    const skorKepadatanN = ((Number(kepadatan) || 0) / maxKepadatan) * 100;

    const defisitLuas = Math.max(0, PERMEN_MIN_LUAS_TAMAN_M2 - luasRthM2);
    const skorLuas = Math.min(100, (defisitLuas / PERMEN_MIN_LUAS_TAMAN_M2) * 100);

    return Math.min(100, (skorDefisitKapita * 0.5) + (skorKepadatanN * 0.3) + (skorLuas * 0.2));
}

// Versi computeSkorPrioritas yang menerima satu baris masterData langsung
// (dipakai oleh applyFilter, sebelum data melewati buildAnalysisTableData).
// Fallback mengikuti pola yang sama seperti buildAnalysisTableData.
function computeItemSkorPrioritas(item){
    const g = geoByNameGlobal[item.nama] || {};
    const kepadatan = Number(item.kepadatan ?? g.kepadatan ?? 0);
    const luasRth = Number(item.luas_rth ?? g.luas_rth_km2 ?? 0);
    const jumlahPenduduk = Number(item.jumlah_penduduk ?? g.jumlah_penduduk ?? 0);
    return computeSkorPrioritas(kepadatan, luasRth * 1000000, jumlahPenduduk);
}

function densityCategory(kepadatanPerKm2){
    const k = Number(kepadatanPerKm2) || 0;
    if (k > 40000) return { label:'Sangat Padat', color:'#8B2C24', bg:'#fdf0ef' };
    if (k > 20000) return { label:'Tinggi',        color:'#D43C33', bg:'#fef2f1' };
    if (k > 15000) return { label:'Sedang',        color:'#E67E22', bg:'#fef6ec' };
    return               { label:'Rendah',        color:'#4d7a4a', bg:'#f0fdf4' };
}

/* =======================================
        GLOBAL STATE
======================================= */

let filteredData = [...masterData];

let activeFilter = {
    kecamatan: null,
    kelurahan: null,
    prioritas: null
};

let tableSearchTerm = "";

/* =======================================
        FILTER ENGINE
======================================= */

function applyFilter(){
    filteredData = masterData.filter(item=>{
        if(activeFilter.kecamatan &&
           item.kecamatan !== activeFilter.kecamatan)
            return false;

        if(activeFilter.gid &&
           item.gid !== activeFilter.gid)
            return false;

        if(activeFilter.prioritas){
            const skorItem = computeItemSkorPrioritas(item);

            if(activeFilter.prioritas === "Tinggi" &&
                skorItem < 60)
                return false;

            if(activeFilter.prioritas === "Sedang" &&
                (skorItem < 35 ||
                 skorItem >= 60))
                return false;

            if(activeFilter.prioritas === "Rendah" &&
                skorItem >= 35)
                return false;
        }

        return true;
    });

    refreshDashboard();
}

function renderCards(){
    const totalKelurahan = filteredData.length;

    const totalPenduduk = filteredData.reduce(
        (sum, item) => sum + (Number(item.jumlah_penduduk) || 0),
        0
    );

    const totalRth = filteredData.reduce(
        (sum, item) => sum + (Number(item.luas_rth) || 0),
        0
    );

    const totalLuas = filteredData.reduce(
        (sum, item) => sum + (Number(item.luas_kelurahan) || 0),
        0
    );

    const persenRth = totalLuas > 0 ? (totalRth / totalLuas) * 100 : 0;
    const rataKepadatan = totalLuas > 0 ? totalPenduduk / totalLuas : 0;
    const rthKapita = totalPenduduk > 0 ? (totalRth * 1000000) / totalPenduduk : 0;

    const belum = filteredData.filter(item=>{
        const luasRthM2 = (Number(item.luas_rth) || 0) * 1000000;
        return !isKelurahanMemenuhi(luasRthM2, Number(item.jumlah_penduduk) || 0);
    }).length;

    const totalDefisit = filteredData.reduce(
        (sum, item)=>{
            const luasRthM2      = (Number(item.luas_rth) || 0) * 1000000;
            const jumlahPenduduk = Number(item.jumlah_penduduk) || 0;
            const requiredM2 = Math.max(
                PERMEN_MIN_RTH_PER_KAPITA_M2 * jumlahPenduduk,
                PERMEN_MIN_LUAS_TAMAN_M2
            );
            const deficitM2 = Math.max(0, requiredM2 - luasRthM2);
            return sum + (deficitM2 / 1000000);
        },
        0
    );

    document.getElementById("cardKelurahan").innerHTML = totalKelurahan;
    document.getElementById("cardPenduduk").innerHTML = totalPenduduk.toLocaleString("id-ID");
    document.getElementById("cardKepadatan").innerHTML = rataKepadatan.toFixed(0);
    document.getElementById("cardRth").innerHTML = persenRth.toFixed(2) + "%";
    document.getElementById("cardKapita").innerHTML = rthKapita.toFixed(2) + " m²";
    document.getElementById("cardBelum").innerHTML = belum;

    document.getElementById("cardRth").className =
        "stat-val " + (persenRth >= 20 ? "c-g" : "c-r");

    document.getElementById("cardDefisit").innerHTML =
        "Defisit " + totalDefisit.toFixed(2) + " km²";

    const changeCard = document.getElementById("changeSummaryCard");
    if (changeCard) {
        let totalRthPrev = 0;
        let naik = 0, turun = 0, tetap = 0;

        filteredData.forEach(item => {
            const diffLuas = item.diff_luas_rth_km2;
            if (diffLuas === null || diffLuas === undefined) return;

            totalRthPrev += (Number(item.luas_rth) || 0) - Number(diffLuas);

            if (item.trend_rth === 'naik') naik++;
            else if (item.trend_rth === 'turun') turun++;
            else if (item.trend_rth === 'tetap') tetap++;
        });

        const persenRthPrev = totalLuas > 0 ? (totalRthPrev / totalLuas) * 100 : 0;
        const diffPersenKota = persenRth - persenRthPrev;

        const naikEl = document.getElementById("changeNaik");
        if (naikEl) naikEl.textContent = naik;
        const turunEl = document.getElementById("changeTurun");
        if (turunEl) turunEl.textContent = turun;
        const tetapEl = document.getElementById("changeTetap");
        if (tetapEl) tetapEl.textContent = tetap;

        changeCard.classList.toggle("is-turun", diffPersenKota < 0);
    }
}

/* =======================================
        CLEAR FILTER
======================================= */

function clearFilter(){
    activeFilter = {
        kecamatan: null,
        kelurahan: null,
        prioritas: null
    };
    filteredData = [...masterData];
    refreshDashboard();
}

function clearKelurahanFilter(){
    activeFilter.gid = null;
    applyFilter();
}

/* =======================================
        REFRESH
======================================= */

function refreshDashboard(){
    renderCards();
    renderRthChart();
    renderRthChangeChart();
    renderPopulationChart();
    renderTrendChart();
    refreshAnalysisTable();
    renderGeoLayer('rth');
    renderGeoLayer('kepadatan');
}

/* ════════════════════════════════════════
   CHART.JS DEFAULTS
════════════════════════════════════════ */
const gc = 'rgba(0,0,0,0.04)';
const tc = '#5a5855';
Chart.defaults.font.family = "'DM Sans', system-ui, sans-serif";
Chart.defaults.font.size   = 11.5;
Chart.defaults.color       = tc;

function colorByRth(pct, alpha) {
  if (pct < 10)  return `rgba(192,57,43,${alpha})`;
  if (pct < 20)  return `rgba(180,83,9,${alpha})`;
  return `rgba(21,128,61,${alpha})`;
}

function colorBySkor(s, alpha) {
  if (s >= 70) return `rgba(192,57,43,${alpha})`;
  if (s >= 45) return `rgba(180,83,9,${alpha})`;
  return `rgba(21,128,61,${alpha})`;
}

/* ════════════════════════════════════════
   TABEL ANALISIS PER KELURAHAN
════════════════════════════════════════ */
const fmtInt = n => Math.round(n || 0).toLocaleString('id-ID');
const fmt2   = n => (Number(n) || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmt3   = n => (Number(n) || 0).toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
const fmt4   = n => (Number(n) || 0).toLocaleString('id-ID', { minimumFractionDigits: 4, maximumFractionDigits: 4 });

function prioritasInfo(skor) {
  if (skor >= 60) return { label: 'Tinggi', cls: 'tinggi' };
  if (skor >= 35) return { label: 'Sedang', cls: 'sedang' };
  return { label: 'Rendah', cls: 'rendah' };
}

function trendCellHtml(diff, trend) {
  if (diff === null || diff === undefined || trend === null || trend === undefined) {
    return `<span style="color:var(--ink3)">–</span>`;
  }
  const arrow = trend === 'naik' ? '▲' : (trend === 'turun' ? '▼' : '▬');
  const cls   = trend === 'naik' ? 'bdg-naik' : (trend === 'turun' ? 'bdg-turun' : 'bdg-tetap');
  const sign  = diff > 0 ? '+' : '';
  return `<span class="badge ${cls}">${arrow} ${sign}${fmt2(diff)}%</span>`;
}

function buildAnalysisTableData(data){
    const geoByName = {};

    (mapGeoJson.features || []).forEach(f=>{
        const p = f.properties || {};
        if(p.nama){
            geoByName[p.nama] = p;
        }
    });

    return data.map(d=>{
        const g = geoByName[d.nama] || {};

        const luas = Number(
            d.luas_kelurahan ??
            g.luas_kelurahan_km2 ??
            0
        );

        const luasRth = Number(
            d.luas_rth ??
            g.luas_rth_km2 ??
            0
        );

        const persenRth = Number(
            d.persen_rth ??
            g.persen_rth ??
            0
        );

        const jumlahPenduduk = Number(
            d.jumlah_penduduk ??
            g.jumlah_penduduk ??
            0
        );

        const kepadatan = Number(
            d.kepadatan ??
            g.kepadatan ??
            0
        );

        const rthPerKapita =
            jumlahPenduduk > 0
            ? (luasRth * 1000000) / jumlahPenduduk
            : 0;

        const luasRthM2 = luasRth * 1000000;
        const memenuhi = isKelurahanMemenuhi(luasRthM2, jumlahPenduduk);
        const defisitM2Kapita = Math.max(0, PERMEN_MIN_RTH_PER_KAPITA_M2 - rthPerKapita);

        const skor = computeSkorPrioritas(kepadatan, luasRthM2, jumlahPenduduk);

        const diffPersenRth =
            (d.diff_persen_rth === null || d.diff_persen_rth === undefined)
            ? null
            : Number(d.diff_persen_rth);

        const trendRth = d.trend_rth ?? null;

        return {
            gid: d.gid,
            nama: d.nama,
            luas,
            rth_km2: luasRth,
            persen_rth: persenRth,
            jumlah_penduduk: jumlahPenduduk,
            kepadatan,
            rth_per_kapita: rthPerKapita,
            defisit_m2_kapita: defisitM2Kapita,
            memenuhi,
            skor,
            diff_persen_rth: diffPersenRth,
            trend_rth: trendRth
        };
    });
}

function refreshAnalysisTable(){
    analysisTableData = buildAnalysisTableData(filteredData);
    renderAnalysisTable();
}

let analysisTableData = [];
const analysisSortState = { key: null, dir: 1 };

function onTableSearch(value){
    tableSearchTerm = (value || "").trim().toLowerCase();
    const wrap = document.getElementById("tableSearchWrap");
    if(wrap){
        wrap.classList.toggle("has-value", tableSearchTerm.length > 0);
    }
    renderAnalysisTable();
}

function clearTableSearch(){
    tableSearchTerm = "";
    const input = document.getElementById("tableSearchInput");
    if(input) input.value = "";
    const wrap = document.getElementById("tableSearchWrap");
    if(wrap) wrap.classList.remove("has-value");
    renderAnalysisTable();
}

function highlightMatch(text, term){
    if(!term) return text;
    const idx = text.toLowerCase().indexOf(term);
    if(idx === -1) return text;
    return text.slice(0, idx) +
        "<mark>" + text.slice(idx, idx + term.length) + "</mark>" +
        text.slice(idx + term.length);
}

function renderAnalysisTable() {
  const tbody = document.getElementById('analysisTableBody');
  const countEl = document.getElementById('tableCount');
  if (!tbody) return;

  let rows = analysisTableData.slice();

  if (tableSearchTerm) {
    rows = rows.filter(r =>
        (r.nama || "").toLowerCase().includes(tableSearchTerm)
    );
  }

  if (countEl) {
    countEl.innerHTML = tableSearchTerm
        ? `<b>${rows.length}</b> dari ${analysisTableData.length} kelurahan`
        : `<b>${analysisTableData.length}</b> kelurahan`;
  }

  if (analysisSortState.key) {
    const key = analysisSortState.key, dir = analysisSortState.dir;
    rows.sort((a, b) => {
      const av = a[key], bv = b[key];
      if (typeof av === 'string') return av.localeCompare(bv, 'id') * dir;
      return (av - bv) * dir;
    });
  }

  if (!rows.length) {
    const msg = tableSearchTerm
        ? `Tidak ada kelurahan yang cocok dengan "${tableSearchTerm}"`
        : 'Data tidak tersedia';
    tbody.innerHTML = `<tr><td colspan="12" style="text-align:center;color:var(--ink3);padding:20px">${msg}</td></tr>`;
    return;
  }

  tbody.innerHTML = rows.map((r, i) => {
    const pr = prioritasInfo(r.skor);
    const namaDisplay = highlightMatch(r.nama, tableSearchTerm);
    const statusCls = r.memenuhi ? 'bdg-aman' : 'bdg-kritis';
    const statusLabel = r.memenuhi ? 'Memenuhi' : 'Belum Memenuhi';
    return `
      <tr onclick="filterKelurahan('${r.gid}')" style="cursor:pointer">
        <td>${i + 1}</td>
        <td class="td-name">${namaDisplay}</td>
        <td>${fmt2(r.luas)}</td>
        <td>${fmt3(r.rth_km2)}</td>
        <td>${fmt4(r.persen_rth)}%</td>
        <td>${trendCellHtml(r.diff_persen_rth, r.trend_rth)}</td>
        <td>${fmtInt(r.jumlah_penduduk)}</td>
        <td>${fmtInt(r.kepadatan/100)}</td>
        <td>${fmt2(r.rth_per_kapita)}</td>
        <td>${fmt2(r.defisit_m2_kapita)}</td>
        <td><span class="badge ${statusCls}">${statusLabel}</span></td>
        <td><span class="badge bdg-prioritas-${pr.cls}">${pr.label}</span></td>
      </tr>`;
  }).join('');

  document.querySelectorAll('#analysisTable th.sortable').forEach(th => {
    const key = th.dataset.key;
    const ic = th.querySelector('.sort-ic');
    if (!ic) return;
    if (key === analysisSortState.key) {
      ic.textContent = analysisSortState.dir === 1 ? '↑' : '↓';
      th.classList.add('sorted');
    } else {
      ic.textContent = '↕';
      th.classList.remove('sorted');
    }
  });
}

function sortAnalysisTable(key) {
  if (analysisSortState.key === key) {
    analysisSortState.dir *= -1;
  } else {
    analysisSortState.key = key;
    analysisSortState.dir = 1;
  }
  renderAnalysisTable();
}

function filterKelurahan(gid){
    if(activeFilter.gid === gid){
        activeFilter.gid = null;
    }else{
        activeFilter.gid = gid;
    }
    applyFilter();
}

/* ── RTH Bar (15 terendah) ── */

let rthChart = null;

function renderRthChart(){
    const ctx = document.getElementById("rthBarChart");
    if(!ctx) return;

    if(rthChart){
        rthChart.destroy();
    }

    const rthData = [...filteredData]
        .sort((a,b)=>a.persen_rth - b.persen_rth)
        .slice(0,15);

    rthChart = new Chart(ctx.getContext("2d"),{
        type:"bar",
        data:{
            labels:rthData.map(d=>
                d.nama.length > 13
                    ? d.nama.slice(0,13) + "…"
                    : d.nama
            ),
            datasets:[{
                data:rthData.map(d=>d.persen_rth),
                backgroundColor:rthData.map(d=>{
                    if(activeFilter.gid === d.gid){
                        return "#2563eb";
                    }
                    return colorByRth(d.persen_rth, 0.85);
                }),
                borderRadius:4,
                borderSkipped:false
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            indexAxis:"y",
            plugins:{
                legend:{display:false},
                tooltip:{
                    callbacks:{
                        label:function(context){
                            return "RTH : " + context.parsed.x.toFixed(2) + " %";
                        }
                    }
                }
            },
            onClick:function(evt, elements){
                if(!elements.length) return;
                const row = rthData[elements[0].index];
                activeFilter.gid = row.gid;
                applyFilter();
            },
            scales:{
                x:{
                    grid:{color:gc},
                    ticks:{
                        callback:v=>v + "%"
                    }
                },
                y:{
                    grid:{display:false},
                    ticks:{font:{size:10.5}}
                }
            }
        }
    });
}

/* ── Perubahan RTH (15 terbesar) ── */

let rthChangeChart = null;

function renderRthChangeChart(){
    const ctx = document.getElementById("rthChangeChart");
    if(!ctx) return;

    if(rthChangeChart){
        rthChangeChart.destroy();
    }

    const changeData = [...filteredData]
        .filter(d => d.diff_persen_rth !== null && d.diff_persen_rth !== undefined)
        .sort((a,b)=>a.diff_persen_rth - b.diff_persen_rth)
        .slice(0,15);

    if(!changeData.length){
        rthChangeChart = null;
        return;
    }

    rthChangeChart = new Chart(ctx.getContext("2d"),{
        type:"bar",
        data:{
            labels:changeData.map(d=>
                d.nama.length > 13
                    ? d.nama.slice(0,13) + "…"
                    : d.nama
            ),
            datasets:[{
                data:changeData.map(d=>d.diff_persen_rth),
                backgroundColor:changeData.map(d=>{
                    if(activeFilter.gid === d.gid){
                        return "#2563eb";
                    }
                    if(d.diff_persen_rth < 0) return "rgba(192,57,43,0.85)";
                    if(d.diff_persen_rth > 0) return "rgba(21,128,61,0.85)";
                    return "rgba(154,152,149,0.6)";
                }),
                borderRadius:4,
                borderSkipped:false
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            indexAxis:"y",
            plugins:{
                legend:{display:false},
                tooltip:{
                    callbacks:{
                        label:function(context){
                            const v = context.parsed.x;
                            return "Perubahan RTH: " + (v>=0?"+":"") + v.toFixed(2) + " poin %";
                        }
                    }
                }
            },
            onClick:function(evt, elements){
                if(!elements.length) return;
                const row = changeData[elements[0].index];
                activeFilter.gid = row.gid;
                applyFilter();
            },
            scales:{
                x:{
                    grid:{color:gc},
                    ticks:{
                        callback:v=>(v>=0?"+":"") + v + "%"
                    }
                },
                y:{
                    grid:{display:false},
                    ticks:{font:{size:10.5}}
                }
            }
        }
    });
}

/* ── Jumlah Penduduk Bar ── */

let populationChart = null;

function renderPopulationChart(){
    const ctx = document.getElementById("popChart");
    if(!ctx) return;

    if(populationChart){
        populationChart.destroy();
    }

    const popData = [...filteredData]
        .sort((a,b)=>b.jumlah_penduduk - a.jumlah_penduduk)
        .slice(0,15);

    populationChart = new Chart(ctx.getContext("2d"),{
        type:"bar",
        data:{
            labels:popData.map(d=>
                d.nama.length > 13
                    ? d.nama.slice(0,13) + "…"
                    : d.nama
            ),
            datasets:[{
                label:"Jumlah Penduduk",
                data: popData.map(x=>x.jumlah_penduduk),
                backgroundColor:popData.map(d=>{
                    if(activeFilter.gid === d.gid){
                        return "#2563eb";
                    }
                    return "rgba(180,83,9,.75)";
                }),
                borderRadius:4,
                borderSkipped:false
            }]
        },
        options:{
            onClick:function(evt, elements){
                if(!elements.length) return;
                const row = popData[elements[0].index];
                activeFilter.gid = row.gid;
                applyFilter();
            },
            responsive:true,
            maintainAspectRatio:false,
            indexAxis:"y",
            plugins:{
                legend:{ display:false },
                tooltip:{
                    callbacks:{
                        label:function(context){
                            const d = popData[context.dataIndex];
                            return [
                                "Jumlah Penduduk : " +
                                d.jumlah_penduduk.toLocaleString("id-ID") +
                                " jiwa"
                            ];
                        }
                    }
                }
            },
            scales:{
                x:{
                    grid:{ color:gc },
                    ticks:{
                        callback:v=>v.toLocaleString("id-ID")
                    }
                },
                y:{
                    grid:{ display:false }
                }
            }
        }
    });
}

/* ── Tren (dual axis) ── */

function buildTrendSeries() {
  const haveYears = Array.isArray(trendData.years)
      ? trendData.years.map(Number).sort((a, b) => a - b)
      : [];
  const targetYears = haveYears.length ? haveYears : [2023, 2024, 2025];
  const rthMap = {};
  const pendudukMap = {};

  haveYears.forEach((y, i) => {
      rthMap[y] = Number(trendData.rth[i]);
      pendudukMap[y] = Number(trendData.jumlah_penduduk[i]);
  });

  const lastTarget = targetYears[targetYears.length - 1];
  const lastYear    = haveYears.length ? Math.max(...haveYears) : lastTarget;
  const baseRth     = rthMap[lastYear] ?? cityStats.persenRth;
  const basePenduduk = pendudukMap[lastYear] ?? cityStats.totalPenduduk;

  const rth = [], jumlahPenduduk = [];
  targetYears.forEach(y => {
    if (rthMap[y] != null) {
      rth.push(rthMap[y]);
    } else {
      const yearsBack = lastTarget - y;
      rth.push(Math.max(0, +(baseRth + yearsBack * 0.6).toFixed(2)));
    }
    if (pendudukMap[y] != null) {
      jumlahPenduduk.push(pendudukMap[y]);
    } else {
      const yearsBack = lastTarget - y;
      jumlahPenduduk.push(Math.max(0, Math.round(basePenduduk - yearsBack * (basePenduduk * 0.015))));
    }
  });

  return { years: targetYears, rth, jumlahPenduduk };
}

let trendChart = null;

function renderTrendChart(){
    const ctx = document.getElementById('trendChart');
    if(!ctx) return;

    if(trendChart){
        trendChart.destroy();
    }

    const trendSeries = buildTrendSeries();

    trendChart = new Chart(ctx.getContext('2d'), {
      type: 'line',
      data: {
        labels: trendSeries.years,
        datasets: [
          {
            label: '% RTH',
            data: trendSeries.rth,
            borderColor: '#15803d',
            backgroundColor: 'rgba(21,128,61,0.06)',
            fill: true, tension: 0.4, yAxisID: 'y',
            pointRadius: 4, pointHoverRadius: 6,
            pointBackgroundColor: '#15803d'
          },
          {
            label: 'Jumlah Penduduk',
            data: trendSeries.jumlahPenduduk,
            borderColor: '#c0392b',
            backgroundColor: 'transparent',
            tension: 0.4, yAxisID: 'y1',
            borderDash: [5, 3],
            pointRadius: 4, pointHoverRadius: 6,
            pointBackgroundColor: '#c0392b'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            type: 'linear',
            position: 'left',
            title: {
              display: true,
              text: 'Persentase RTH (%)'
            },
            grid: { color: gc },
            ticks: {
              callback: value => value.toFixed(1) + '%'
            }
          },
          y1: {
            type: 'linear',
            position: 'right',
            title: {
              display: true,
              text: 'Jumlah Penduduk (jiwa)'
            },
            grid: { drawOnChartArea: false },
            ticks: {
              callback: value => value.toLocaleString('id-ID')
            }
          },
          x: {
            grid: { color: gc },
            title: {
              display: true,
              text: 'Tahun'
            }
          }
        }
      }
    });
}

/* ════════════════════════════════════════
   PETA LEAFLET
════════════════════════════════════════ */

const mapInstances = {};

function createMapInstance(mode, containerId){
    const map = L.map(containerId, {
        center: [-6.2383, 107.0],
        zoom: 12,
        scrollWheelZoom: true,
        zoomControl: true
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd', maxZoom: 19
    }).addTo(map);

    const instance = {
        map,
        geoLayer: null,
        labelLayer: null,
        suppressPopupClose: false
    };

    map.on('zoomend', function(){
        updateLabelVisibility(mode);
    });

    mapInstances[mode] = instance;
    return instance;
}

createMapInstance('rth', 'rth-map-rth');
createMapInstance('kepadatan', 'rth-map-kepadatan');

function getColor(props, mode) {
  if (mode === 'rth') {
    const luasRthM2 = (Number(props.luas_rth_km2) || 0) * 1000000;
    const jumlahPenduduk = Number(props.jumlah_penduduk) || 0;
    return isKelurahanMemenuhi(luasRthM2, jumlahPenduduk) ? "#2E7D32" : "#D32F2F";
  }

  if (mode === "kepadatan") {
      return densityCategory(props.kepadatan).color;
  }

  return '#94a3b8';
}

function hoverTipHtml(p, mode) {
  const fmt = n => n != null ? Number(n).toLocaleString('id-ID') : '—';

  if (mode === 'kepadatan') {
    const kepadatanHa = (Number(p.kepadatan) || 0) / 100;
    const cat = densityCategory(p.kepadatan);
    const jumlahPenduduk = Number(p.jumlah_penduduk) || 0;
    const luasRthM2 = (Number(p.luas_rth_km2) || 0) * 1000000;
    const rthPerKapita = jumlahPenduduk > 0 ? luasRthM2 / jumlahPenduduk : 0;
    const skor = computeSkorPrioritas(p.kepadatan, luasRthM2, jumlahPenduduk);

    return `
      <div class="rth-popup" style="min-width:190px">
        <div class="pop-name">${p.nama || '—'}</div>
        <div class="pop-kec">
          Kec. ${p.kecamatan || '—'}
          <span class="badge" style="background:${cat.bg};color:${cat.color}">${cat.label}</span>
        </div>
        <table>
          <tr><td>Kepadatan</td><td>${fmt(kepadatanHa.toFixed(0))} jiwa/ha</td></tr>
          <tr><td>Jumlah Penduduk</td><td>${fmt(p.jumlah_penduduk)} jiwa</td></tr>
          <tr><td>Luas Kelurahan</td><td>${p.luas_kelurahan_km2 ?? '—'} km²</td></tr>
          <tr><td>RTH per Kapita</td><td>${rthPerKapita.toFixed(2)} m²/jiwa</td></tr>
          <tr><td>Skor Prioritas</td><td>${skor.toFixed(1)}</td></tr>
        </table>
      </div>
    `;
  }

  const luasRthM2 = (Number(p.luas_rth_km2) || 0) * 1000000;
  const jumlahPenduduk = Number(p.jumlah_penduduk) || 0;
  const rthPerKapita = jumlahPenduduk > 0 ? luasRthM2 / jumlahPenduduk : 0;
  const memenuhi = isKelurahanMemenuhi(luasRthM2, jumlahPenduduk);

  const rthBadge = memenuhi
      ? `<span class="badge bdg-aman">Memenuhi</span>`
      : `<span class="badge bdg-kritis">Belum Memenuhi</span>`;
  const skor = computeSkorPrioritas(p.kepadatan, luasRthM2, jumlahPenduduk);

  return `
    <div class="rth-popup" style="min-width:190px">
      <div class="pop-name">${p.nama || '—'}</div>
      <div class="pop-kec">Kec. ${p.kecamatan || '—'} ${rthBadge}</div>
      <table>
        <tr><td>RTH per Kapita</td><td>${rthPerKapita.toFixed(2)} m²/jiwa</td></tr>
        <tr><td>Luas RTH</td><td>${p.luas_rth_km2 ?? '—'} km²</td></tr>
        <tr><td>Luas Kelurahan</td><td>${p.luas_kelurahan_km2 ?? '—'} km²</td></tr>
        <tr><td>Jumlah Penduduk</td><td>${fmt(p.jumlah_penduduk)} jiwa</td></tr>
        <tr><td>Kepadatan</td><td>${fmt((p.kepadatan/100).toFixed(0))} jiwa/ha</td></tr>
        <tr><td>Skor Prioritas</td><td>${skor.toFixed(1)}</td></tr>
      </table>
    </div>
  `;
}

function popupHtml(p, mode) {
  const fmt = n => n != null ? Number(n).toLocaleString('id') : '—';

  if (mode === 'kepadatan') {
    const kepadatanHa = (Number(p.kepadatan) || 0) / 100;
    const cat = densityCategory(p.kepadatan);
    const jumlahPenduduk = Number(p.jumlah_penduduk) || 0;
    const luasRthM2 = (Number(p.luas_rth_km2) || 0) * 1000000;
    const rthPerKapita = jumlahPenduduk > 0 ? luasRthM2 / jumlahPenduduk : 0;
    const skor = computeSkorPrioritas(p.kepadatan, luasRthM2, jumlahPenduduk);

    return `
      <div class="rth-popup">
        <div class="pop-name">${p.nama || '—'}</div>
        <div class="pop-kec">
          Kec. ${p.kecamatan || '—'}
          <span class="badge" style="background:${cat.bg};color:${cat.color}">${cat.label}</span>
        </div>
        <table>
          <tr><td>Kepadatan</td><td>${kepadatanHa.toFixed(0)} jiwa/ha</td></tr>
          <tr><td>Jumlah Penduduk</td><td>${fmtInt(p.jumlah_penduduk)} jiwa</td></tr>
          <tr><td>Luas Kelurahan</td><td>${p.luas_kelurahan_km2 ?? '—'} km²</td></tr>
          <tr><td>RTH per Kapita</td><td>${rthPerKapita.toFixed(2)} m²/jiwa</td></tr>
          <tr><td>Skor Prioritas</td><td>${skor.toFixed(1)}</td></tr>
        </table>
      </div>
    `;
  }

  const luasRthM2 = (Number(p.luas_rth_km2) || 0) * 1000000;
  const jumlahPenduduk = Number(p.jumlah_penduduk) || 0;
  const rthPerKapita = jumlahPenduduk > 0 ? luasRthM2 / jumlahPenduduk : 0;
  const memenuhi = isKelurahanMemenuhi(luasRthM2, jumlahPenduduk);

  const badge = memenuhi
      ? `<span class="badge bdg-aman">Memenuhi</span>`
      : `<span class="badge bdg-kritis">Belum Memenuhi</span>`;
  const skor = computeSkorPrioritas(p.kepadatan, luasRthM2, jumlahPenduduk);

  return `
    <div class="rth-popup">
      <div class="pop-name">${p.nama || '—'}</div>
      <div class="pop-kec">Kec. ${p.kecamatan || '—'} ${badge}</div>
      <table>
        <tr><td>RTH per Kapita</td><td>${rthPerKapita.toFixed(2)} m²/jiwa</td></tr>
        <tr><td>Luas RTH</td>        <td>${p.luas_rth_km2 ?? '—'} km²</td></tr>
        <tr><td>Luas Kelurahan</td>  <td>${p.luas_kelurahan_km2 ?? '—'} km²</td></tr>
        <tr><td>Jumlah Penduduk</td>       <td>${fmtInt(p.jumlah_penduduk)} jiwa</td></tr>
        <tr><td>Skor Prioritas</td>  <td>${skor.toFixed(1)}</td></tr>
      </table>
    </div>
  `;
}

function renderLabelLayer(instance, geoFiltered){
    if(instance.labelLayer){
        instance.map.removeLayer(instance.labelLayer);
    }

    instance.labelLayer = L.layerGroup();

    geoFiltered.features.forEach(feature=>{
        try{
            const tmp = L.geoJSON(feature);
            const center = tmp.getBounds().getCenter();
            const nama = (feature.properties && feature.properties.nama) || '';

            if(!nama) return;

            L.marker(center,{
                icon: L.divIcon({
                    className: 'kel-label',
                    html: nama,
                    iconSize: [0,0]
                }),
                interactive: false,
                keyboard: false
            }).addTo(instance.labelLayer);
        }catch(e){
            // skip invalid geometry
        }
    });

    instance.labelLayer.addTo(instance.map);
}

function updateLabelVisibility(mode){
    const instance = mapInstances[mode];
    if(!instance) return;

    const show = instance.map.getZoom() >= 11;
    const containerId = mode === 'rth' ? 'rth-map-rth' : 'rth-map-kepadatan';
    const el = document.getElementById(containerId);

    if(el){
        el.classList.toggle('hide-labels', !show);
    }
}

function renderGeoLayer(mode){
    const instance = mapInstances[mode];
    if(!instance || !mapGeoJson.features) return;

    if(instance.geoLayer){
        instance.suppressPopupClose = true;
        instance.map.removeLayer(instance.geoLayer);
        instance.suppressPopupClose = false;
    }

    const gids = filteredData.map(x => x.gid);

    const geoFiltered = {
        type: "FeatureCollection",
        features: mapGeoJson.features.filter(f =>
            gids.includes(f.properties.gid)
        )
    };

    instance.geoLayer = L.geoJSON(geoFiltered,{
        style: function(feature){
            return {
                fillColor: getColor(feature.properties, mode),
                fillOpacity: 0.72,
                color: "#ffffff",
                weight: 1.2,
                opacity: 1
            };
        },
        onEachFeature: function(feature, layer){
            layer.bindPopup(
                popupHtml(feature.properties, mode),
                {
                    maxWidth: 270,
                    className: "rth-lp"
                }
            );

            layer.bindTooltip(
                hoverTipHtml(feature.properties, mode),
                {
                    sticky: true,
                    direction: "top",
                    offset: [0, -6],
                    opacity: 1,
                    className: "rth-hover-tip"
                }
            );

            layer.on("click", function(){
                if(activeFilter.gid === feature.properties.gid){
                    activeFilter.gid = null;
                }else{
                    activeFilter.gid = feature.properties.gid;
                }
                applyFilter();
            });

            layer.on({
                mouseover: function(e){
                    e.target.setStyle({
                        fillOpacity: 0.9,
                        weight: 2,
                        color: "#111827"
                    });
                    e.target.bringToFront();
                },
                mouseout: function(e){
                    instance.geoLayer.resetStyle(e.target);
                }
            });

            layer.on("popupclose", function(){
                if(instance.suppressPopupClose) return;
                clearKelurahanFilter();
            });
        }
    }).addTo(instance.map);

    renderLabelLayer(instance, geoFiltered);

    if(geoFiltered.features.length){
        instance.map.fitBounds(
            instance.geoLayer.getBounds(),
            { padding: [15, 15] }
        );
    }
}

/* ════════════════════════════════════════
   INIT
════════════════════════════════════════ */

renderCards();
renderRthChart();
renderRthChangeChart();
renderPopulationChart();
renderTrendChart();
refreshAnalysisTable();
renderGeoLayer('rth');
renderGeoLayer('kepadatan');

setTimeout(() => {
    mapInstances.rth.map.invalidateSize();
    mapInstances.kepadatan.map.invalidateSize();
}, 200);

/* ════════════════════════════════════════
   HELPERS
════════════════════════════════════════ */
function setSection(el, title, desc) {
  document.querySelectorAll('.s-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('section-title').textContent = title;
  document.getElementById('section-desc').textContent  = desc || '';
}

function changeYear(year) {
  const url = new URL(window.location.href);
  url.searchParams.set('year', year);
  window.location.href = url.toString();
}
</script>
@endpush