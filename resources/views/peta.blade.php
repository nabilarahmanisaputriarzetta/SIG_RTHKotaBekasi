@extends('layouts.app')
@section('title', 'Peta Interaktif - SIG RTH Publik Kota Bekasi')

@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
@endsection

@section('extra-styles')
<style>

/* =====================================================================
   ROOT VARS
===================================================================== */
:root {
    --green-50:    #f0fdf4;
    --green-100:   #dcfce7;
    --green-500:   #22c55e;
    --green-600:   #16a34a;
    --green-700:   #15803d;
    --green-800:   #166534;
    --orange-50:   #fff7ed;
    --orange-100:  #ffedd5;
    --orange-500:  #f97316;
    --orange-600:  #ea580c;
    --orange-700:  #c2410c;
    --text-dark:   #111827;
    --text-mid:    #374151;
    --text-light:  #9ca3af;
    --border:      #e5e7eb;
    --border-mid:  #d1d5db;
    --red:         #dc2626;
    --red-50:      #fef2f2;
    --bg-page:     #f9fafb;
    --shadow-sm:   0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.05);
    --shadow-md:   0 4px 8px rgba(0,0,0,.07), 0 12px 32px rgba(0,0,0,.08);
    --radius-sm:   6px;
    --radius-md:   10px;
    --radius-lg:   14px;
    --violet-50:   #f5f3ff;
    --violet-100:  #ede9fe;
    --violet-500:  #8b5cf6;
    --violet-600:  #7c3aed;
    --violet-700:  #6d28d9;
    --violet-800:  #4c1d95;
    --blue-50:     #eff6ff;
    --blue-100:    #dbeafe;
    --blue-600:    #2563eb;
    --blue-700:    #1d4ed8;
    --sidebar-accent: var(--green-700);
    --tp: 0.32s cubic-bezier(0.32,0.72,0,1);
}

body.mode-kepadatan   { --sidebar-accent: var(--orange-700); }

/* =====================================================================
   LAYOUT
===================================================================== */
/* Halaman peta didesain full-viewport (app-like): footer global dari
   layout disembunyikan khusus di halaman ini supaya tidak ikut ter-scroll
   masuk ke atas peta/sidebar (lihat screenshot: footer "Nabila..." muncul
   menimpa drawer). Kalau footer memang harus tetap tampil di halaman ini,
   kabari saya — override ini gampang dicabut. */
footer { display: none !important; }

.peta-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 64px);
    overflow: hidden;
    background: var(--bg-page, #f9fafb);
}

/* =====================================================================
   TOPBAR
===================================================================== */
.peta-topbar {
    flex-shrink: 0;
    background: white;
    border-bottom: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    z-index: 10;
}

.peta-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 11px 20px 9px;
    border-bottom: 1px solid var(--border);
    gap: 12px;
}

.peta-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-icon {
    width: 34px; height: 34px;
    border-radius: var(--radius-sm);
    background: var(--green-50);
    border: 1px solid var(--green-100);
    display: flex; align-items: center; justify-content: center;
    color: var(--green-700); flex-shrink: 0;
    transition: transform .2s ease, box-shadow .2s ease;
}
.header-icon:hover { transform: scale(1.06); box-shadow: 0 0 0 3px var(--green-100); }

.peta-header-left h1 { font-size: 14px; font-weight: 700; color: var(--text-dark); margin: 0 0 1px; line-height: 1.3; }
.peta-header-left p  { font-size: 11px; color: var(--text-light); margin: 0; }

.header-actions { display: flex; align-items: center; gap: 8px; }

/* ── Inline action buttons (Bandingkan di peta-controls) ── */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 13px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all 0.18s ease;
    white-space: nowrap;
    font-family: inherit;
    height: 30px;
}
.btn-ghost {
    background: transparent;
    color: var(--text-mid);
    border-color: var(--border);
}
.btn-ghost:hover {
    background: var(--bg-page);
    border-color: var(--border-mid);
}
.btn-primary {
    background: var(--green-600);
    color: #fff;
    border-color: var(--green-700);
    box-shadow: 0 1px 4px rgba(22,163,74,0.2);
}
.btn-primary:hover {
    background: var(--green-700);
    box-shadow: 0 3px 10px rgba(22,163,74,0.3);
}
.btn-primary.is-compare-open {
    background: var(--green-800);
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
}

.peta-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    overflow-x: auto;
    scrollbar-width: none;
    min-height: 52px;
}
.peta-controls::-webkit-scrollbar { display: none; }

.ctrl-group { display: flex; flex-direction: column; gap: 3px; flex-shrink: 0; }
.ctrl-label { font-size: 10px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: .07em; }
.ctrl-select-wrap { position: relative; display: flex; align-items: center; }
.ctrl-icon { position: absolute; left: 8px; color: var(--text-light); pointer-events: none; }

.ctrl-select {
    padding: 5px 26px 5px 26px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: inherit; font-size: 12px; font-weight: 500;
    color: var(--text-dark); outline: none; background: white;
    cursor: pointer; transition: border-color .15s, box-shadow .15s;
    min-width: 110px; height: 30px;
    appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='11' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 8px center;
}
.ctrl-select:hover { border-color: var(--green-500); }
.ctrl-select:focus { border-color: var(--green-500); box-shadow: 0 0 0 3px rgba(34,197,94,.12); }

.ctrl-divider { width: 1px; height: 28px; background: var(--border); flex-shrink: 0; margin: 0 4px; }

.overlay-toggle {
    display: flex; background: var(--bg-page);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    overflow: hidden; height: 30px; padding: 3px; gap: 2px;
}
.overlay-btn {
    display: flex; align-items: center; gap: 5px; padding: 0 11px;
    font-family: inherit; font-size: 12px; font-weight: 600;
    cursor: pointer; border: none; background: transparent;
    color: var(--text-light); border-radius: 4px;
    transition: background .18s, color .18s; white-space: nowrap;
}
.overlay-btn.active { background: var(--sidebar-accent); color: white; box-shadow: 0 1px 4px rgba(0,0,0,.12); }
.overlay-btn:not(.active):hover { background: var(--green-50); color: var(--green-700); }
body.mode-kepadatan .overlay-btn:not(.active):hover { background: var(--orange-50); color: var(--orange-700); }

/* =====================================================================
   BODY
===================================================================== */
.peta-body { flex: 1; display: flex; overflow: hidden; position: relative; }
#map { flex: 1; height: 100%; z-index: 1; }

/* =====================================================================
   SIDEBAR
===================================================================== */
.peta-sidebar {
    width: 292px; flex-shrink: 0;
    border-left: 1px solid var(--border); background: white;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: -2px 0 16px rgba(0,0,0,.06);
    transition: transform var(--tp);
}
@media (min-width: 1280px) { .peta-sidebar { width: 316px; } }

.sidebar-panel {
    display: none; flex-direction: column; flex: 1;
    overflow: hidden; min-height: 0;
    animation: panel-in .25s ease;
}
@keyframes panel-in { from { opacity: 0; transform: translateX(8px); } to { opacity: 1; transform: none; } }
.sidebar-panel--active { display: flex; }

.sidebar-summary {
    padding: 14px 16px 12px; border-bottom: 1px solid var(--border);
    flex-shrink: 0;
    background: linear-gradient(160deg, var(--green-50) 0%, white 100%);
}
.sidebar-summary--padat { background: linear-gradient(160deg, var(--orange-50) 0%, white 100%); }

.ss-toprow { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }

.ss-icon-wrap {
    width: 32px; height: 32px; border-radius: var(--radius-sm);
    background: var(--green-50); border: 1px solid var(--green-100);
    display: flex; align-items: center; justify-content: center;
    color: var(--green-700); flex-shrink: 0; transition: transform .2s;
}
.ss-icon-wrap:hover { transform: scale(1.08); }
.ss-icon-wrap--padat { background: var(--orange-50); border-color: var(--orange-100); color: var(--orange-700); }

.ss-title-block { flex: 1; min-width: 0; }
.ss-city { font-size: 13px; font-weight: 700; color: var(--text-dark); margin: 0 0 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ss-subtitle { font-size: 11px; color: var(--text-light); margin: 0; }

.ss-badge {
    padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 600;
    white-space: nowrap; flex-shrink: 0;
}
.ss-badge--green { background: var(--green-100); color: var(--green-700); }
.ss-badge--padat { background: var(--orange-100); color: var(--orange-700); }

.ss-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }

.ss-metric {
    background: var(--bg-page); border: 1px solid var(--border);
    border-radius: var(--radius-sm); padding: 7px 9px;
    transition: border-color .18s, transform .18s, box-shadow .18s; cursor: default;
}
.ss-metric:hover { border-color: var(--green-500); transform: translateY(-1px); box-shadow: 0 3px 8px rgba(22,163,74,.08); }

.ss-m-label { display: block; font-size: 10px; color: var(--text-light); text-transform: uppercase; letter-spacing: .05em; font-weight: 600; margin-bottom: 2px; }
.ss-m-value { display: block; font-size: 13px; font-weight: 700; color: var(--text-dark); line-height: 1.3; transition: opacity .3s; }
.ss-pct { font-size: 15px; }

.ss-status-row { display: flex; align-items: stretch; border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; }
.ss-status-item { flex: 1; display: flex; flex-direction: column; align-items: center; padding: 8px 6px; gap: 3px; transition: filter .15s; }
.ss-status-item:hover { filter: brightness(.97); }
.ss-status-ok      { background: var(--green-50); }
.ss-status-no      { background: var(--red-50); }
.ss-status-danger { background: #fff1f2; }
.ss-status-ok-padat { background: #f0fdf4; }

.ss-status-num {
    font-size: 20px; font-weight: 800; line-height: 1; color: var(--text-dark);
    transition: transform .3s cubic-bezier(0.34,1.56,0.64,1);
}
.ss-status-num.bump { transform: scale(1.3); }
.ss-status-ok .ss-status-num      { color: var(--green-700); }
.ss-status-no .ss-status-num      { color: var(--red); }
.ss-status-danger .ss-status-num { color: #9f1239; }
.ss-status-ok-padat .ss-status-num { color: var(--green-600); }
.ss-status-label { font-size: 10px; color: var(--text-light); display: flex; align-items: center; gap: 3px; text-align: center; line-height: 1.3; }
.ss-status-divider { width: 1px; background: var(--border); }

.ss-progress-wrap { padding: 12px 16px 14px; border-bottom: 1px solid var(--border); flex-shrink: 0; }
.ss-prog-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
.ss-prog-label { font-size: 11px; font-weight: 600; color: var(--text-mid); }
.ss-prog-val   { font-size: 12px; font-weight: 800; padding: 3px 10px; border-radius: 20px; background: var(--green-50); color: var(--green-700); transition: color .35s, background-color .35s; }
.ss-prog-val--padat { color: var(--orange-700); background: var(--orange-50); }

.ss-prog-track { position: relative; height: 9px; background: var(--border); border-radius: 5px; overflow: visible; margin-bottom: 5px; }
.ss-prog-fill {
    height: 100%; border-radius: 5px;
    background: linear-gradient(90deg, var(--green-500), var(--green-700));
    transition: width .8s cubic-bezier(0.23,1,0.32,1);
    position: relative;
    box-shadow: 0 1px 4px rgba(22,163,74,.35);
}
.ss-prog-fill::after { content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 6px; border-radius: 0 5px 5px 0; background: rgba(255,255,255,.35); }

.ss-prog-target { position: absolute; top: -8px; left: calc(100% - 1px); width: 2px; height: 17px; background: repeating-linear-gradient(180deg, var(--green-800) 0 3px, transparent 3px 5px); }
.ss-prog-target::before { content: ''; position: absolute; top: -6px; left: 50%; transform: translateX(-50%); border: 4px solid transparent; border-top-color: var(--green-800); }
.ss-prog-target::after { content: 'Target 20%'; position: absolute; bottom: 12px; right: -3px; font-size: 8.5px; font-weight: 700; color: var(--green-800); white-space: nowrap; background: var(--green-50); padding: 1px 6px; border-radius: 8px; border: 1px solid var(--green-100); }
.ss-prog-ticks { display: flex; justify-content: space-between; font-size: 9px; color: var(--text-light); margin-top: 6px; padding: 0 2px; }

.kp-dist-bars { display: flex; flex-direction: column; gap: 5px; margin-top: 4px; }
.kp-dist-row { display: grid; grid-template-columns: 80px 1fr 26px; align-items: center; gap: 6px; }
.kp-dist-name { font-size: 10px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kp-dist-track { height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
.kp-dist-fill { height: 100%; border-radius: 3px; transition: width .7s cubic-bezier(0.23,1,0.32,1); }
.kp-dist-count { font-size: 10px; font-weight: 700; color: var(--text-mid); text-align: right; }

.sidebar-list-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 16px; background: #fafafa;
    border-bottom: 1px solid var(--border); cursor: pointer; user-select: none;
    flex-shrink: 0; position: sticky; top: 0; z-index: 2;
    font-size: 11px; font-weight: 700; color: var(--text-mid);
    text-transform: uppercase; letter-spacing: .06em;
    transition: background .15s;
}
.sidebar-list-header:hover { background: var(--green-50); }
.sidebar-list-header:focus-visible { outline: 2px solid var(--green-500); outline-offset: -2px; }

.sl-sort-label { font-size: 10px; font-weight: 600; color: var(--text-light); }
.sl-sort-btn {
    width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;
    border: 1.5px solid var(--border); border-radius: 4px; background: white;
    cursor: pointer; color: var(--text-mid);
    transition: background .15s, border-color .15s, transform .2s; padding: 0;
}
.sl-sort-btn:hover { background: var(--green-50); border-color: var(--green-500); color: var(--green-700); transform: rotate(180deg); }

.sl-chevron { transition: transform .25s ease; color: var(--text-light); }
.sidebar-list-header.collapsed .sl-chevron { transform: rotate(-90deg); }

.sidebar-list { flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; scrollbar-width: thin; scrollbar-color: var(--border-mid) transparent; min-height: 0; }
.sidebar-list::-webkit-scrollbar { width: 3px; }
.sidebar-list::-webkit-scrollbar-thumb { background: var(--border-mid); border-radius: 2px; }

/* RTH item */
.sidebar-kel-item {
    padding: 10px 16px; border-bottom: 1px solid #f3f4f6;
    cursor: pointer; transition: background .14s, transform .14s;
    position: relative; overflow: hidden;
}
.sidebar-kel-item::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 0; background: var(--green-500); opacity: .15; transition: width .2s; }
.sidebar-kel-item:hover::before { width: 3px; }
.sidebar-kel-item:hover { background: var(--green-50); transform: translateX(2px); }
.sidebar-kel-item.highlighted { background: var(--green-100); border-left: 3px solid var(--green-600); padding-left: 13px; }

.ski-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 6px; }
.ski-name { font-size: 12px; font-weight: 600; color: var(--text-dark); line-height: 1.3; }
.ski-sub  { font-size: 10px; color: var(--text-light); margin-top: 1px; }
.ski-rek  { font-size: 10.5px; line-height: 1.5; color: var(--text-mid); margin-top: 6px; padding-top: 6px; border-top: 1px dashed var(--border); display: flex; align-items: flex-start; gap: 5px; }
.ski-rek svg { flex-shrink: 0; color: var(--text-light); margin-top: 1px; }
.ski-rek-score { font-weight: 800; flex-shrink: 0; }
.ski-rank-badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 9.5px; font-weight: 800; letter-spacing: .01em;
    padding: 2px 7px; border-radius: 999px; flex-shrink: 0;
    background: var(--violet-100); color: var(--violet-700);
    white-space: nowrap;
}
.ski-rank-badge--padat { background: var(--orange-100); color: var(--orange-700); }
.ski-rek-need { display: block; margin-top: 3px; font-weight: 700; color: var(--text-dark); }

.rek-disclaimer { border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); background: var(--bg-page); }
.rek-disclaimer-toggle {
    width: 100%; display: flex; align-items: center; gap: 6px; padding: 7px 14px;
    background: none; border: none; cursor: pointer; font-family: inherit;
    font-size: 10.5px; font-weight: 600; color: var(--text-mid); text-align: left;
}
.rek-disclaimer-toggle svg.rd-icon { flex-shrink: 0; color: var(--text-light); }
.rek-disclaimer-toggle span { flex: 1; }
.rek-disclaimer-toggle svg.rd-chevron { flex-shrink: 0; color: var(--text-light); transition: transform .2s ease; }
.rek-disclaimer-toggle[aria-expanded="true"] svg.rd-chevron { transform: rotate(180deg); }
.rek-disclaimer-body { display: none; padding: 0 14px 10px 34px; font-size: 10.5px; line-height: 1.5; color: var(--text-mid); }
.rek-disclaimer-body.open { display: block; }
.rek-disclaimer-body b { color: var(--text-dark); }
.ski-pct-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
    white-space: nowrap;
    transition: transform .18s ease;
}
.ski-pct-badge:hover { transform: scale(1.05); }
.ski-pct-ideal  { background: #dcfce7; color: #166534; }
.ski-pct-baik   { background: var(--green-50); color: var(--green-800); }
.ski-pct-sedang { background: #fef9c3; color: #854d0e; }
.ski-pct-rendah { background: #ffedd5; color: #9a3412; }
.ski-pct-kritis { background: #fee2e2; color: #991b1b; }
.ski-bar  { height: 3px; background: var(--border); border-radius: 2px; margin-top: 5px; overflow: hidden; }
.ski-bar-fill { height: 100%; border-radius: 2px; transition: width .6s cubic-bezier(0.23,1,0.32,1); }

/* Kepadatan item */
.kp-kel-item {
    padding: 9px 16px; border-bottom: 1px solid #f3f4f6;
    cursor: pointer; transition: background .14s, transform .14s;
    position: relative; overflow: hidden;
}
.kp-kel-item::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 0; background: var(--orange-500); opacity: .15; transition: width .2s; }
.kp-kel-item:hover::before { width: 3px; }
.kp-kel-item:hover { background: var(--orange-50); transform: translateX(2px); }
.kp-kel-item.highlighted { background: var(--orange-100); border-left: 3px solid var(--orange-600); padding-left: 13px; }

.kpi-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 6px; }
.kpi-name { font-size: 12px; font-weight: 600; color: var(--text-dark); line-height: 1.3; }
.kpi-cat  { font-size: 10px; color: var(--text-light); margin-top: 1px; }
.kpi-val  { font-size: 12px; font-weight: 700; flex-shrink: 0; text-align: right; }
.kpi-bar  { height: 3px; background: var(--border); border-radius: 2px; margin-top: 5px; overflow: hidden; }
.kpi-bar-fill { height: 100%; border-radius: 2px; transition: width .6s cubic-bezier(0.23,1,0.32,1); }

.kel-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 32px 16px; color: var(--text-light); font-size: 12px; text-align: center; }
.kel-empty p { margin: 0; }
.kel-empty svg { opacity: .4; animation: pulse-empty 2s ease-in-out infinite; }
@keyframes pulse-empty { 0%,100% { opacity: .4; } 50% { opacity: .65; } }

.leg-title { font-size: 10px; font-weight: 700; color: var(--text-mid); text-transform: uppercase; letter-spacing: .06em; margin: 0 0 8px; }
.leg-scale { display: flex; flex-direction: column; gap: 4px; }
.leg-item { display: flex; align-items: center; gap: 7px; font-size: 11px; color: var(--text-mid); transition: transform .14s; }
.leg-item:hover { transform: translateX(2px); }
.leg-swatch { width: 13px; height: 13px; border-radius: 3px; flex-shrink: 0; border: 1px solid rgba(0,0,0,.08); box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.leg-tag { font-style: normal; font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 10px; margin-left: 2px; }
.leg-tag--ok     { background: var(--green-100); color: var(--green-800); }
.leg-tag--bad    { background: #fee2e2; color: #991b1b; }
.leg-tag--danger { background: #fce7e7; color: #7f1d1d; }
.leg-tag--sedang { background: #fef3c7; color: #92400e; }
.leg-note { font-size: 10px; color: var(--text-light); margin: 8px 0 0; font-style: italic; }

.sidebar-handle { display: none; width: 36px; height: 4px; background: var(--border); border-radius: 2px; margin: 10px auto 4px; flex-shrink: 0; cursor: grab; transition: background .2s; }
.sidebar-handle:active { cursor: grabbing; background: var(--green-500); }

/* =====================================================================
   BUTTONS
===================================================================== */
.btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 0 14px; height: 32px; border: 1.5px solid transparent;
    border-radius: var(--radius-sm); font-family: inherit;
    font-size: 12px; font-weight: 600; cursor: pointer;
    text-decoration: none; white-space: nowrap; flex-shrink: 0;
    transition: background .15s, box-shadow .15s, transform .12s;
    position: relative; overflow: hidden;
}
.btn::after { content: ''; position: absolute; inset: 0; border-radius: inherit; opacity: 0; background: rgba(255,255,255,.18); transition: opacity .15s; }
.btn:active::after { opacity: 1; }

.btn-primary { background: var(--green-700); color: white; box-shadow: 0 1px 6px rgba(21,128,61,.2); }
.btn-primary:hover { background: var(--green-800); box-shadow: 0 3px 12px rgba(21,128,61,.3); transform: translateY(-1px); }
.btn-primary:active { transform: scale(.97); }
.btn-primary.is-compare-open { background: var(--green-800); box-shadow: inset 0 1px 4px rgba(0,0,0,.2); }

.btn-ghost { background: white; border-color: var(--border); color: var(--text-mid); }
.btn-ghost:hover { background: var(--bg-page); border-color: var(--border-mid); transform: translateY(-1px); }
.btn-ghost:active { transform: scale(.97); }
.btn-ghost:hover svg { animation: spin-once .5s ease; }
@keyframes spin-once { from { transform: rotate(0); } to { transform: rotate(-360deg); } }

.btn-label { font-size: 12px; }

/* =====================================================================
   MAP OVERLAYS
===================================================================== */
.map-search { position: absolute; top: 12px; left: 12px; z-index: 500; }
.map-search-inner { position: relative; display: flex; align-items: center; }

.map-search input {
    padding: 7px 12px 7px 34px; border-radius: var(--radius-md);
    border: 1.5px solid var(--border); font-family: inherit; font-size: 12px;
    width: 200px; box-shadow: var(--shadow-md); outline: none; background: white;
    transition: border-color .15s, width .25s, box-shadow .2s;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: 10px center;
}
.map-search input:focus { border-color: var(--green-500); width: 240px; box-shadow: 0 0 0 3px rgba(34,197,94,.12), var(--shadow-md); }

.map-search-clear {
    position: absolute; right: 8px; width: 18px; height: 18px;
    border: none; background: var(--border); border-radius: 50%;
    cursor: pointer; color: var(--text-light);
    display: none; align-items: center; justify-content: center;
    font-size: 11px; line-height: 1; transition: background .15s, color .15s;
}
.map-search-clear:hover { background: var(--border-mid); color: var(--text-dark); }
.map-search-clear.visible { display: flex; }

.map-loading {
    position: absolute; inset: 0; z-index: 999;
    background: rgba(255,255,255,.78);
    display: flex; align-items: center; justify-content: center;
    gap: 8px; font-size: 13px; color: var(--text-mid);
    pointer-events: none; backdrop-filter: blur(3px);
    transition: opacity .25s;
}
.map-loading.hidden { opacity: 0; pointer-events: none; }
.map-loading.fully-hidden { display: none; }

.spinner { width: 18px; height: 18px; border: 2px solid var(--border); border-top-color: var(--green-600); border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.map-year-badge {
    position: absolute; bottom: 32px; left: 12px; z-index: 400;
    background: white; border: 1.5px solid var(--border);
    border-radius: var(--radius-md); padding: 5px 11px;
    font-size: 11px; font-weight: 700; color: var(--text-mid);
    box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 6px;
}
.map-year-badge-dot {
    width: 7px; height: 7px; border-radius: 50%; background: var(--green-600);
    animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot { 0%,100% { box-shadow: 0 0 0 0 rgba(22,163,74,.4); } 50% { box-shadow: 0 0 0 5px rgba(22,163,74,0); } }

/* ── Map legend (floating, kiri bawah peta) ── */
.mobile-legend-wrap {
    position: absolute;
    bottom: 70px;
    left: 12px;
    z-index: 450;
    width: 202px;
    pointer-events: none;
    transition: transform .18s ease, box-shadow .18s ease;
}
.mobile-legend {
    background: rgba(255,255,255,.95);
    border: 1px solid rgba(229,231,235,.95);
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,.12), 0 2px 8px rgba(0,0,0,.06);
    backdrop-filter: blur(6px);
    padding: 11px 13px 12px;
    pointer-events: auto;
    transition: transform .18s ease, box-shadow .18s ease;
}
.mobile-legend:hover { transform: translateY(-2px); box-shadow: 0 14px 36px rgba(0,0,0,.15), 0 3px 10px rgba(0,0,0,.07); }
.mobile-legend .leg-scale { gap: 3px; }
.mobile-legend .leg-item { font-size: 10px; }
.mobile-legend .leg-title { margin-bottom: 6px; }

/* =====================================================================
   COMPARE PANEL
===================================================================== */
.compare-panel {
    position: absolute; top: 12px; right: 12px; z-index: 500;
    background: white; border-radius: var(--radius-lg);
    width: 300px; box-shadow: var(--shadow-md); border: 1px solid var(--border);
    display: flex; flex-direction: column;
    max-height: calc(100% - 24px); overflow: hidden;
    opacity: 0;
    pointer-events: none;
}
.compare-panel.open { opacity: 1; pointer-events: all; }

.cp-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 14px 14px 12px; border-bottom: 1px solid var(--border);
    background: linear-gradient(160deg, var(--green-50) 0%, white 100%); flex-shrink: 0;
}
.cp-header-left { display: flex; align-items: center; gap: 9px; }
.cp-header-icon { width: 30px; height: 30px; border-radius: var(--radius-sm); background: var(--green-50); border: 1px solid var(--green-100); display: flex; align-items: center; justify-content: center; color: var(--green-700); flex-shrink: 0; }
.cp-header h3 { font-size: 13px; font-weight: 700; color: var(--text-dark); margin: 0 0 1px; }
.cp-header p  { font-size: 11px; color: var(--text-light); margin: 0; }

.cp-close {
    width: 26px; height: 26px; display: flex; align-items: center; justify-content: center;
    background: none; border: 1.5px solid var(--border); border-radius: 6px;
    cursor: pointer; color: var(--text-light); flex-shrink: 0;
}
.cp-close:hover { background: #fee2e2; border-color: #fca5a5; color: var(--red); }

.cp-year-row { display: flex; align-items: flex-end; gap: 6px; padding: 12px 14px; border-bottom: 1px solid var(--border); background: var(--bg-page); flex-shrink: 0; }
.cp-year-pick { display: flex; flex-direction: column; gap: 3px; flex: 1; }
.cp-year-label { font-size: 10px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: .06em; }

.cp-select {
    width: 100%; padding: 5px 8px;
    border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    font-family: inherit; font-size: 12px; font-weight: 500;
    outline: none; height: 30px; background: white; color: var(--text-dark);
    transition: border-color .15s, box-shadow .15s; cursor: pointer;
}
.cp-select:hover { border-color: var(--green-500); }
.cp-select:focus { border-color: var(--green-500); box-shadow: 0 0 0 3px rgba(34,197,94,.12); }

.cp-arrow { color: var(--text-light); padding-bottom: 6px; flex-shrink: 0; animation: arrow-pulse 1.6s ease-in-out infinite; }
@keyframes arrow-pulse { 0%,100% { transform: translateX(0); opacity: .5; } 50% { transform: translateX(3px); opacity: 1; } }

.cp-go-btn {
    flex-shrink: 0; align-self: flex-end;
    display: inline-flex; align-items: center; gap: 5px;
    padding: 0 12px; height: 30px;
    background: var(--green-700); color: white;
    border: 1.5px solid var(--green-800);
    border-radius: var(--radius-sm); font-family: inherit;
    font-size: 12px; font-weight: 700; cursor: pointer;
    box-shadow: 0 1px 6px rgba(21,128,61,.2);
    transition: background .15s, transform .12s, box-shadow .15s;
}
.cp-go-btn:hover { background: var(--green-800); transform: translateY(-1px); box-shadow: 0 3px 10px rgba(21,128,61,.3); }
.cp-go-btn:active { transform: scale(.97); }
.cp-go-btn.loading { pointer-events: none; opacity: .75; }
.cp-go-btn.loading svg { animation: spin .7s linear infinite; }

.cp-body {
    flex: 1; overflow-y: auto; overflow-x: hidden;
    -webkit-overflow-scrolling: touch; overscroll-behavior: contain;
    scrollbar-width: thin; scrollbar-color: var(--border-mid) transparent;
    padding: 12px 14px; display: flex; flex-direction: column; gap: 10px;
}
.cp-body::-webkit-scrollbar { width: 3px; }
.cp-body::-webkit-scrollbar-thumb { background: var(--border-mid); border-radius: 2px; }

.cp-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }

.cp-chip {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    padding: 8px 4px; border-radius: var(--radius-md); border: 1px solid var(--border);
    transition: transform .2s, box-shadow .2s; cursor: default;
}
.cp-chip:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,.08); }
.cp-chip-down { background: var(--red-50); border-color: #fca5a5; }
.cp-chip-same { background: var(--bg-page); }
.cp-chip-up   { background: var(--green-50); border-color: var(--green-100); }
.cp-chip-num  { font-size: 20px; font-weight: 800; line-height: 1; transition: transform .3s cubic-bezier(0.34,1.56,0.64,1); }
.cp-chip-down .cp-chip-num { color: var(--red); }
.cp-chip-same .cp-chip-num { color: var(--text-light); }
.cp-chip-up   .cp-chip-num { color: var(--green-700); }
.cp-chip-label { font-size: 10px; color: var(--text-light); font-weight: 600; display: flex; align-items: center; gap: 2px; text-transform: uppercase; letter-spacing: .04em; }

.cp-totals { border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; }
.cp-total-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; font-size: 11px; border-bottom: 1px solid #f3f4f6; transition: background .14s; }
.cp-total-row:last-child { border-bottom: none; }
.cp-total-row:hover { background: var(--bg-page); }
.cp-total-diff { background: var(--green-50); border-top: 1px dashed var(--green-100) !important; }
.cp-total-label { color: var(--text-light); }
.cp-total-label b { color: var(--text-dark); font-weight: 700; }
.cp-total-val { font-weight: 600; color: var(--text-dark); }

.cp-list-header { display: flex; justify-content: space-between; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-light); padding: 2px 0 4px; }

.cp-list { border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; max-height: 200px; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; scrollbar-width: thin; }
.cp-list-item { display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; border-bottom: 1px solid #f3f4f6; font-size: 11px; transition: background .14s; cursor: pointer; }
.cp-list-item:last-child { border-bottom: none; }
.cp-list-item:hover { background: var(--bg-page); }
.cp-list-item.highlighted { background: var(--green-100); border-left: 3px solid var(--green-600); padding-left: 7px; }
.trend-up   { color: var(--green-600); }
.trend-down { color: var(--red); }
.trend-same { color: var(--text-light); }

.cp-hint { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 0 8px; color: var(--text-light); font-size: 12px; text-align: center; }
.cp-hint svg { opacity: .35; }
.cp-hint p { margin: 0; line-height: 1.5; }

/* =====================================================================
   LEAFLET POPUP & TOOLTIP
===================================================================== */
.leaflet-popup-content-wrapper { border-radius: var(--radius-md) !important; box-shadow: 0 4px 20px rgba(0,0,0,.12) !important; padding: 0 !important; overflow: hidden; }
.leaflet-popup-content { margin: 0 !important; min-width: 180px; }
.popup-inner { padding: 10px 12px 12px; }
.popup-title { font-size: 12px; font-weight: 700; color: var(--text-dark); margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid var(--border); line-height: 1.3; }
.popup-kec { font-size: 10px; font-weight: 500; color: var(--text-light); display: block; margin-top: 1px; }
.popup-row { display: flex; justify-content: space-between; align-items: center; font-size: 11px; padding: 3px 0; gap: 12px; }
.popup-row + .popup-row { border-top: 1px solid #f3f4f6; }
.pk { color: var(--text-light); }
.pv { font-weight: 600; color: var(--text-dark); }
.popup-badge-wrap { display: flex; gap: 5px; margin-top: 8px; padding-top: 7px; border-top: 1px solid var(--border); flex-wrap: wrap; }
.popup-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 700; }
.badge-ok     { background: #dcfce7; color: #166534; }
.badge-no     { background: #fee2e2; color: #991b1b; }
.badge-padat  { background: #ffedd5; color: #9a3412; }
.badge-sangat { background: #fce7e7; color: #7f1d1d; }
.badge-sedang { background: #fef9c3; color: #854d0e; }
.badge-rendah { background: var(--green-50); color: var(--green-800); }

.peta-tooltip { background: white; border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,.12); padding: 0; font-family: 'DM Sans', system-ui, sans-serif; pointer-events: none; max-width: 240px; }
.peta-tooltip::before,
.leaflet-tooltip-left.peta-tooltip::before,
.leaflet-tooltip-right.peta-tooltip::before { display: none; }

/* =====================================================================
   FAB
===================================================================== */
.sidebar-toggle-btn {
    display: none; position: absolute; bottom: 20px; right: 20px; z-index: 600;
    width: 46px; height: 46px; border-radius: 50%;
    background: var(--sidebar-accent); color: white; border: none; cursor: pointer;
    box-shadow: 0 4px 16px rgba(21,128,61,.4);
    align-items: center; justify-content: center;
    transition: transform .2s, box-shadow .2s;
}
.sidebar-toggle-btn:hover { transform: scale(1.07); box-shadow: 0 6px 20px rgba(21,128,61,.5); }
.sidebar-toggle-btn:active { transform: scale(.95); }

/* =====================================================================
   TOAST
===================================================================== */
.peta-toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%) translateY(12px);
    background: var(--text-dark); color: white;
    padding: 8px 18px; border-radius: 20px;
    font-size: 12px; font-weight: 600; z-index: 9000;
    opacity: 0; pointer-events: none;
    transition: opacity .2s, transform .2s;
    white-space: nowrap; box-shadow: 0 4px 16px rgba(0,0,0,.2);
}
.peta-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

/* =====================================================================
   RESPONSIVE
===================================================================== */
@media (max-width: 768px) {
    .peta-header-left p { display: none; }
    .peta-header-left h1 { font-size: 13px; }
    .peta-controls { padding: 7px 14px; gap: 8px; }
    .ctrl-select { min-width: 88px; font-size: 11px; }
    .peta-body { flex-direction: column; }
    .ctrl-title-block {
        display: flex;
        flex-direction: column;
        gap: 1px;
        flex-shrink: 0;
        margin-right: 4px;
    }
    .ctrl-title-main {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary, #111827);
        white-space: nowrap;
    }
    .ctrl-title-sub {
        font-size: 10px;
        color: var(--text-muted, #6b7280);
        white-space: nowrap;
    }
    #map { flex: 1; height: 100%; }
    .peta-sidebar {
        position: absolute; bottom: 0; left: 0; right: 0;
        width: 100%; height: 56vh; max-height: 480px; border-left: none;
        border-top: 1px solid var(--border);
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -8px 32px rgba(0,0,0,.12);
        transform: translateY(100%);
        transition: transform var(--tp); z-index: 500;
    }
    .peta-sidebar.drawer-open { transform: translateY(0); }
    .sidebar-handle { display: block; }
    .sidebar-toggle-btn { display: flex; width: 42px; height: 42px; bottom: 16px; right: 16px; }
    /* Saat drawer terbuka, geser FAB ke atas drawer supaya tidak menimpa
       konten sidebar (mis. tombol "Tentang skor rekomendasi") */
    .sidebar-toggle-btn.fab-drawer-open { bottom: calc(56vh + 14px); }

    /* Ringkas isi sidebar supaya drawer tidak makan tempat & tetap scrollable */
    .sidebar-summary { padding: 10px 12px 8px; }
    .ss-metrics { gap: 6px; margin-bottom: 8px; }
    .ss-metric { padding: 5px 8px; }
    .ss-m-value { font-size: 12px; }
    .ss-pct { font-size: 13px; }
    .ss-status-item { padding: 6px 4px; gap: 2px; }
    .ss-status-num { font-size: 16px; }
    .ss-status-label { font-size: 9px; }
    .ss-progress-wrap { padding: 8px 12px 10px; }
    .ss-prog-header { margin-bottom: 14px; }

    /* Panel bandingkan tahun: dibuat kartu kecil nempel kanan-atas (bukan
       full-width) supaya sebagian besar peta tetap kelihatan di sekitarnya,
       dan isinya tetap bisa discroll */
    .compare-panel {
        width: min(260px, calc(100vw - 24px));
        left: auto; right: 12px; top: 12px;
        max-height: min(44vh, calc(100% - 24px));
    }
    .cp-header { padding: 8px 10px 7px; }
    .cp-header-icon { width: 24px; height: 24px; }
    .cp-header h3 { font-size: 12px; }
    .cp-header p { font-size: 10px; }
    .cp-close { width: 22px; height: 22px; }
    .cp-year-row { padding: 8px 10px; gap: 5px; }
    .cp-select { height: 26px; font-size: 11px; padding: 4px 6px; }
    .cp-go-btn { height: 26px; padding: 0 9px; font-size: 11px; }
    .cp-body { padding: 8px 10px; gap: 7px; }
    .cp-summary { gap: 4px; }
    .cp-chip { padding: 5px 3px; }
    .cp-chip-num { font-size: 15px; }
    .cp-chip-label { font-size: 9px; }
    .cp-total-row { padding: 6px 9px; font-size: 10px; }
    .cp-list { max-height: 100px; }
    .cp-list-item { padding: 5px 8px; font-size: 10px; }

    .map-search input { width: 160px; }
    .map-year-badge { bottom: 70px; }

    /* Legenda: jangan direntangkan full-width, cukup kartu kecil menempel kiri */
    .mobile-legend-wrap {
        top: 88px;
        bottom: auto;
        left: 12px;
        right: auto;
        width: min(160px, calc(100vw - 24px));
    }
    .mobile-legend { padding: 7px 9px 8px; }
    .mobile-legend .leg-title { font-size: 9px; margin-bottom: 5px; }
    .mobile-legend .leg-scale { gap: 2px; }
    .mobile-legend .leg-item { font-size: 8.5px; gap: 5px; }
    .mobile-legend .leg-swatch { width: 9px; height: 9px; }
    .mobile-legend .leg-note { font-size: 8px; margin-top: 5px; }
    .mobile-legend .leg-tag { font-size: 7px; padding: 1px 3px; }

    /* Popup & tooltip kelurahan mengikuti lebar layar, bukan fixed 240px */
    .leaflet-popup-content-wrapper { max-width: calc(100vw - 48px) !important; }
    .leaflet-popup-content { min-width: 150px; }
    .popup-inner { padding: 8px 10px 9px; }
    .popup-title { font-size: 10.5px; }
    .popup-row { font-size: 10px; }
    .peta-tooltip { max-width: min(190px, calc(100vw - 48px)); }
}

@media (max-width: 480px) {
    .overlay-btn { padding: 0 8px; font-size: 11px; }
    .btn { padding: 0 10px; font-size: 11px; height: 28px; }
    .btn-label { display: none; }
    .map-search { top: 8px; left: 8px; }
    .map-search input { width: 140px; font-size: 11px; }
    .header-icon { width: 30px; height: 30px; }
    .peta-header { padding: 9px 14px 7px; }
    .peta-sidebar { height: 60vh; }
    .sidebar-toggle-btn.fab-drawer-open { bottom: calc(60vh + 14px); }
    .compare-panel {
        width: min(230px, calc(100vw - 16px));
        left: auto; right: 8px; top: 8px;
        max-height: min(48vh, calc(100% - 16px));
    }
    .cp-list { max-height: 84px; }

    /* Kartu-kartu makin dipadatkan di layar sangat sempit */
    .ss-metrics { gap: 5px; }
    .ss-metric { padding: 4px 6px; }
    .ss-m-label { font-size: 9px; }
    .ss-m-value { font-size: 11px; }
    .ss-status-item { padding: 5px 3px; }
    .ss-status-num { font-size: 14px; }
    .cp-summary { gap: 4px; }
    .cp-chip { padding: 5px 3px; }
    .cp-chip-num { font-size: 14px; }
    .cp-chip-label { font-size: 9px; }

    .mobile-legend-wrap { width: min(140px, calc(100vw - 16px)); top: 80px; left: 8px; }
    .mobile-legend { padding: 6px 8px 7px; }
    .mobile-legend .leg-title { font-size: 8px; }
    .mobile-legend .leg-item { font-size: 8px; }
    .mobile-legend .leg-swatch { width: 8px; height: 8px; }
    .mobile-legend .leg-note { display: none; }
    .mobile-legend .leg-tag { display: none; }

    .leaflet-popup-content-wrapper { max-width: calc(100vw - 32px) !important; }
    .leaflet-popup-content { min-width: 140px; }
    .popup-inner { padding: 7px 9px 8px; }
    .popup-title { font-size: 10px; }
    .popup-row { font-size: 9.5px; gap: 8px; }
}

@keyframes shake {
    0%,100% { transform: translateX(0); }
    20%      { transform: translateX(-5px); }
    40%      { transform: translateX(5px); }
    60%      { transform: translateX(-4px); }
    80%      { transform: translateX(4px); }
}

</style>
@endsection

@section('content')
<div class="peta-container">

    {{-- ─── TOPBAR ─── --}}
    <div class="peta-topbar">
        <div class="peta-controls" role="toolbar" aria-label="Kontrol peta">

            {{-- Tahun --}}
            <div class="ctrl-group">
                <label class="ctrl-label" for="selTahun">Tahun Data</label>
                <div class="ctrl-select-wrap">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" class="ctrl-icon" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8"  y1="2" x2="8"  y2="6"/>
                        <line x1="3"  y1="10" x2="21" y2="10"/>
                    </svg>
                    <select id="selTahun" class="ctrl-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == 2025 ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="ctrl-divider" aria-hidden="true"></div>

            {{-- Overlay toggle --}}
            <div class="ctrl-group">
                <label class="ctrl-label" id="overlayLabel">Overlay Peta</label>
                <div class="overlay-toggle" role="group" aria-labelledby="overlayLabel">
                    <button class="overlay-btn active" id="btnRth"
                            onclick="setOverlay('rth')" aria-pressed="true">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2C12 2 6 8 6 14C6 17.3 8.7 20 12 20C15.3 20 18 17.3 18 14C18 8 12 2 12 2Z"/>
                        </svg>
                        RTH
                    </button>
                    <button class="overlay-btn" id="btnKepadatan"
                            onclick="setOverlay('kepadatan')" aria-pressed="false">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3
                                    3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34
                                    5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8
                                    0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                        Kepadatan
                    </button>
                </div>
            </div>

            <div class="ctrl-divider" id="filterRthDivider" aria-hidden="true"></div>
            <div class="ctrl-group" id="filterRthGroup">
                <label class="ctrl-label" for="selFilter">Filter RTH</label>
                <div class="ctrl-select-wrap">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" class="ctrl-icon" aria-hidden="true">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    <select id="selFilter" class="ctrl-select" onchange="applyFilter()">
                        <option value="all">Semua Kelurahan</option>
                        <option value="below">Belum Memenuhi Standar</option>
                        <option value="above">Memenuhi Standar</option>
                    </select>
                </div>
            </div>

            <div class="ctrl-divider" aria-hidden="true"></div>

            {{-- Action buttons --}}
            <button class="btn btn-primary" onclick="toggleCompare()"
                    aria-controls="comparePanel" aria-expanded="false" id="btnCompare">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M10 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h5v2h2V1h-2v2zm0 15H5l5-6v6zm9-15h-5v2h5v13l-5-6v8h5c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
                </svg>
                <span class="btn-label">Bandingkan</span>
            </button>

        </div>

    </div>

    {{-- ─── BODY ─── --}}
    <div class="peta-body">
        <div style="position:relative;flex:1;height:100%;">
            <div id="map"></div>

            <div class="map-loading hidden" id="mapLoading" role="status" aria-live="polite">
                <div class="spinner" aria-hidden="true"></div>
                Memuat data peta…
            </div>

            <div class="map-search">
                <div class="map-search-inner">
                    <input type="text" id="searchKel" placeholder="Cari kelurahan…"
                           oninput="filterSearch(this.value)" autocomplete="off" aria-label="Cari kelurahan">
                    <button class="map-search-clear" id="searchClear" onclick="clearSearch()" aria-label="Hapus pencarian">✕</button>
                </div>
            </div>

            <div class="map-year-badge" id="mapYearBadge">
                <div class="map-year-badge-dot"></div>
                <span id="mapYearLabel">{{ $year ?? 2025 }}</span>
            </div>

            {{-- Legenda peta — floating card di pojok kiri --}}
            <div id="mobileLegendWrap" class="mobile-legend-wrap" aria-label="Legenda">
                <div class="mobile-legend" id="mobileLegendRth">
                    <p class="leg-title">Status RTH Kelurahan</p>
                    <div class="leg-scale">
                        <div class="leg-item"><span class="leg-swatch" style="background:#166534"></span><span>Memenuhi <em class="leg-tag leg-tag--ok">≥ 0,30 m²/jiwa & ≥ 9.000 m²</em></span></div>
                        <div class="leg-item"><span class="leg-swatch" style="background:#dc2626"></span><span>Belum Memenuhi <em class="leg-tag leg-tag--bad">Standar</em></span></div>
                    </div>
                    <p class="leg-note">Standar per kelurahan: Permen PU No.05/PRT/M/2008 (RTH ≥ 0,30 m²/jiwa & luas ≥ 9.000 m²). Target 20% hanya berlaku agregat kota.</p>
                </div>

                <div class="mobile-legend" id="mobileLegendKepadatan" style="display:none;">
                    <p class="leg-title">Kepadatan (jiwa/ha)</p>
                    <div class="leg-scale">
                        <div class="leg-item"><span class="leg-swatch" style="background:#7f1d1d"></span><span>&gt; 400 <em class="leg-tag leg-tag--danger">Sangat Padat</em></span></div>
                        <div class="leg-item"><span class="leg-swatch" style="background:#dc2626"></span><span>201 - 400 <em class="leg-tag leg-tag--bad">Tinggi</em></span></div>
                        <div class="leg-item"><span class="leg-swatch" style="background:#f97316"></span><span>151 - 200 <em class="leg-tag leg-tag--sedang">Sedang</em></span></div>
                        <div class="leg-item"><span class="leg-swatch" style="background:#86efac"></span><span>&lt; 151 <em class="leg-tag leg-tag--ok">Rendah</em></span></div>
                    </div>
                    <p class="leg-note">Standar: SNI 03-1733-2004</p>
                </div>

                <div class="mobile-legend" id="mobileLegendTransisi" style="display:none;">
                    <p class="leg-title">Transisi Status RTH</p>
                    <div class="leg-scale">
                        <div class="leg-item"><span class="leg-swatch" style="background:#166534"></span><span>Konsisten Memenuhi <em class="leg-tag leg-tag--ok">Stabil</em></span></div>
                        <div class="leg-item"><span class="leg-swatch" style="background:#3b82f6"></span><span>Membaik <em class="leg-tag leg-tag--ok">Belum → Memenuhi</em></span></div>
                        <div class="leg-item"><span class="leg-swatch" style="background:#f97316"></span><span>Memburuk <em class="leg-tag leg-tag--bad">Memenuhi → Belum</em></span></div>
                        <div class="leg-item"><span class="leg-swatch" style="background:#dc2626"></span><span>Konsisten Belum Memenuhi <em class="leg-tag leg-tag--danger">Kritis</em></span></div>
                    </div>
                    <p class="leg-note">Status per tahun mengacu Permen PU No.05/PRT/M/2008. Ini komparasi status antar dua tahun untuk kelurahan yang sama — bukan overlay geometri.</p>
                </div>
            </div>

            {{-- Compare panel --}}
            <div class="compare-panel" id="comparePanel"
                 role="dialog" aria-modal="false"
                 aria-label="Panel perbandingan tahun RTH" aria-hidden="true">

                <div class="cp-header">
                    <div class="cp-header-left">
                        <div class="cp-header-icon" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h5v2h2V1h-2v2zm0 15H5l5-6v6zm9-15h-5v2h5v13l-5-6v8h5c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
                            </svg>
                        </div>
                        <div><h3>Bandingkan Tahun</h3><p>Perubahan RTH antar periode</p></div>
                    </div>
                    <button class="cp-close" onclick="toggleCompare()" aria-label="Tutup panel perbandingan">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="cp-year-row">
                    <div class="cp-year-pick">
                        <label class="cp-year-label" for="cpYear1">Tahun Awal</label>
                        <select class="cp-select" id="cpYear1">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ ($y == ($year ?? $years[0] ?? null)) ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cp-arrow" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </div>
                    <div class="cp-year-pick">
                        <label class="cp-year-label" for="cpYear2">Tahun Akhir</label>
                        <select class="cp-select" id="cpYear2">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $y == 2025 ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="cp-go-btn" id="cpGoBtn" onclick="handleLoadCompare()" aria-label="Tampilkan perbandingan">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        Tampilkan
                    </button>
                </div>

                <div class="cp-body" id="cpBody">
                    <div class="cp-hint" id="cpHint">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M10 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h5v2h2V1h-2v2zm0 15H5l5-6v6zm9-15h-5v2h5v13l-5-6v8h5c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
                        </svg>
                        <p>Pilih dua tahun yang berbeda<br>lalu tekan <strong>Tampilkan</strong></p>
                    </div>

                    <div class="cp-summary" id="cpSummary" style="display:none;" aria-live="polite">
                        <div class="cp-chip cp-chip-down">
                            <span class="cp-chip-num" id="cpTurun">-</span>
                            <span class="cp-chip-label"><svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 10l5 5 5-5z"/></svg>Turun</span>
                        </div>
                        <div class="cp-chip cp-chip-same">
                            <span class="cp-chip-num" id="cpTetap">-</span>
                            <span class="cp-chip-label">Tetap</span>
                        </div>
                        <div class="cp-chip cp-chip-up">
                            <span class="cp-chip-num" id="cpNaik">-</span>
                            <span class="cp-chip-label"><svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 14l5-5 5 5z"/></svg>Naik</span>
                        </div>
                    </div>

                    <div class="cp-totals" id="cpTotals" style="display:none;">
                        <div class="cp-total-row"><span class="cp-total-label">Total RTH <b id="cpY1Label"></b></span><span class="cp-total-val" id="cpTot1"></span></div>
                        <div class="cp-total-row"><span class="cp-total-label">Total RTH <b id="cpY2Label"></b></span><span class="cp-total-val" id="cpTot2"></span></div>
                        <div class="cp-total-row cp-total-diff"><span class="cp-total-label">Selisih</span><span class="cp-total-val" id="cpSelisih"></span></div>
                    </div>

                    <div class="cp-list-header" id="cpListHeaderWrap" style="display:none;">
                        <span>Per Kelurahan</span><span>Δ Persen RTH</span>
                    </div>
                    <div class="cp-list" id="cpList" aria-label="Perubahan RTH per kelurahan" style="display:none;"></div>
                </div>
            </div>

            <button class="sidebar-toggle-btn" id="sidebarToggleBtn" onclick="toggleMobileSidebar()" aria-label="Lihat data">
                <svg id="fabIconOpen" width="20" height="20" viewBox="0 0 24 24" fill="white" aria-hidden="true">
                    <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                </svg>
                <svg id="fabIconClose" width="20" height="20" viewBox="0 0 24 24" fill="white" aria-hidden="true" style="display:none;">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        </div>

        {{-- ─── SIDEBAR ─── --}}
        <aside class="peta-sidebar" id="petaSidebar" aria-label="Panel data peta">
            <div class="sidebar-handle" id="sidebarHandle" aria-hidden="true"></div>

            {{-- RTH Panel --}}
            <div id="sidebarRth" class="sidebar-panel sidebar-panel--active">
                <div class="sidebar-summary">
                    <div class="ss-toprow">
                        <div class="ss-icon-wrap" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2C8 8 6 11.5 6 14a6 6 0 0 0 12 0c0-2.5-2-6-6-12z"/>
                            </svg>
                        </div>
                        <div class="ss-title-block">
                            <p class="ss-city">Kota Bekasi</p>
                            <p class="ss-subtitle">Ruang Terbuka Hijau</p>
                        </div>
                        <span class="ss-badge ss-badge--green" id="ssBadge">- kel.</span>
                    </div>
                    <div class="ss-metrics">
                        <div class="ss-metric"><span class="ss-m-label">Luas Wilayah</span><span class="ss-m-value" id="ssWilayah">-</span></div>
                        <div class="ss-metric"><span class="ss-m-label">Total RTH</span><span class="ss-m-value" id="ssRth">-</span></div>
                        <div class="ss-metric"><span class="ss-m-label">% RTH Publik Kota</span><span class="ss-m-value ss-pct" id="ssPct">-</span></div>
                    </div>
                    <div class="ss-status-row">
                        <div class="ss-status-item ss-status-ok">
                            <div class="ss-status-num" id="ssMemenuhi">0</div>
                            <div class="ss-status-label"><svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Memenuhi Standar</div>
                        </div>
                        <div class="ss-status-divider" aria-hidden="true"></div>
                        <div class="ss-status-item ss-status-no">
                            <div class="ss-status-num" id="ssDibawah">0</div>
                            <div class="ss-status-label"><svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>Belum Memenuhi</div>
                        </div>
                    </div>
                </div>

                <div class="rek-disclaimer">
                    <button class="rek-disclaimer-toggle" onclick="toggleRekDisclaimer(this)" aria-expanded="false" aria-controls="rekDisclaimerRth">
                        <svg class="rd-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <span>Tentang skor rekomendasi</span>
                        <svg class="rd-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="rek-disclaimer-body" id="rekDisclaimerRth">
                        Tiap kelurahan dinilai terhadap standar <b>Permen PU No.05/PRT/M/2008</b>: RTH per kapita ≥ 0,30 m²/jiwa <b>dan</b> luas RTH ≥ 9.000 m². Kalau kelurahan yang berbatasan langsung dengannya juga belum memenuhi standar ini, itu ikut disebutkan di kartu — sebagai konteks bahwa kekurangan RTH di kawasan tersebut bisa jadi pola yang lebih luas, bukan cuma masalah satu kelurahan sendirian. Daftar di bawah diurutkan berdasarkan persentase RTH (bisa diganti lewat tombol urutan), bukan berdasarkan peringkat.
                    </div>
                </div>

                <div class="sidebar-list-header" id="sidebarSecHeader"
                     onclick="toggleSidebarSection(this)" role="button" tabindex="0" aria-expanded="true"
                     onkeydown="if(event.key==='Enter'||event.key===' '){this.click();event.preventDefault();}">
                    <span>Kelurahan</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="sl-sort-label" id="slSortLabel">Terbesar →</span>
                        <button class="sl-sort-btn" onclick="event.stopPropagation(); cycleSortMode()" title="Ganti urutan" aria-label="Ganti urutan">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M7 12h10M11 18h2"/></svg>
                        </button>
                        <svg class="sl-chevron" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
                    </div>
                </div>

                <div class="sidebar-list" id="kelList" role="list" aria-label="Daftar kelurahan">
                    <div class="kel-empty">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <p>Memuat data kelurahan…</p>
                    </div>
                </div>

            </div>

            {{-- Kepadatan Panel --}}
            <div id="sidebarKepadatan" class="sidebar-panel" aria-hidden="true">
                <div class="sidebar-summary sidebar-summary--padat">
                    <div class="ss-toprow">
                        <div class="ss-icon-wrap ss-icon-wrap--padat" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                            </svg>
                        </div>
                        <div class="ss-title-block">
                            <p class="ss-city">Kota Bekasi</p>
                            <p class="ss-subtitle">Kepadatan Penduduk</p>
                        </div>
                        <span class="ss-badge ss-badge--padat" id="kpBadge">- kel.</span>
                    </div>
                    <div class="ss-metrics">
                        <div class="ss-metric"><span class="ss-m-label">Total Penduduk</span><span class="ss-m-value" id="kpTotalPenduduk">-</span></div>
                        <div class="ss-metric"><span class="ss-m-label">Luas Wilayah</span><span class="ss-m-value" id="kpLuasWilayah">-</span></div>
                        <div class="ss-metric"><span class="ss-m-label">Kepadatan Rata-rata</span><span class="ss-m-value" id="kpRataRata">-</span></div>
                        <div class="ss-metric"><span class="ss-m-label">Kel. Terpadat</span><span class="ss-m-value" id="kpTerpadat">-</span></div>
                    </div>
                </div>

                <div class="ss-progress-wrap">
                    <div class="ss-prog-header">
                        <span class="ss-prog-label">Distribusi Kategori Kepadatan</span>
                        <span class="ss-prog-val ss-prog-val--padat" id="kpDistLabel">-</span>
                    </div>
                    <div class="kp-dist-bars">
                        <div class="kp-dist-row"><span class="kp-dist-name" style="color:#7f1d1d">Sangat Padat</span><div class="kp-dist-track"><div class="kp-dist-fill" id="kpBarSP" style="background:#7f1d1d;width:0%"></div></div><span class="kp-dist-count" id="kpCountSP">0</span></div>
                        <div class="kp-dist-row"><span class="kp-dist-name" style="color:#dc2626">Tinggi</span><div class="kp-dist-track"><div class="kp-dist-fill" id="kpBarP" style="background:#dc2626;width:0%"></div></div><span class="kp-dist-count" id="kpCountP">0</span></div>
                        <div class="kp-dist-row"><span class="kp-dist-name" style="color:#f97316">Sedang</span><div class="kp-dist-track"><div class="kp-dist-fill" id="kpBarS" style="background:#f97316;width:0%"></div></div><span class="kp-dist-count" id="kpCountS">0</span></div>
                        <div class="kp-dist-row"><span class="kp-dist-name" style="color:#15803d">Rendah</span><div class="kp-dist-track"><div class="kp-dist-fill" id="kpBarR" style="background:#86efac;width:0%"></div></div><span class="kp-dist-count" id="kpCountR">0</span></div>
                    </div>
                </div>

                <div class="rek-disclaimer">
                    <button class="rek-disclaimer-toggle" onclick="toggleRekDisclaimer(this)" aria-expanded="false" aria-controls="rekDisclaimerKp">
                        <svg class="rd-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <span>Tentang skor rekomendasi</span>
                        <svg class="rd-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="rek-disclaimer-body" id="rekDisclaimerKp">
                        Tiap kelurahan dikategorikan kepadatannya mengacu <b>SNI 03-1733-2004</b> (Rendah / Sedang / Tinggi / Sangat Padat), lalu diberi perkiraan kasar seberapa besar kapasitasnya masih bisa menampung tambahan penduduk — <b>banyak</b>, <b>sedang</b>, atau <b>cukup terbatas</b> — tanpa membuat kepadatannya naik kategori atau RTH per kapitanya turun di bawah standar Permen PU No.05/PRT/M/2008 (0,30 m²/jiwa). Ini estimasi kasar dari data luas wilayah dan jumlah penduduk, bukan hasil kajian tata ruang resmi.
                    </div>
                </div>

                <div class="sidebar-list-header" id="kpSecHeader"
                     onclick="toggleSidebarSection(this, 'kpList')" role="button" tabindex="0" aria-expanded="true"
                     onkeydown="if(event.key==='Enter'||event.key===' '){this.click();event.preventDefault();}">
                    <span>Kelurahan</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="sl-sort-label">Terpadat →</span>
                        <svg class="sl-chevron" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
                    </div>
                </div>

                <div class="sidebar-list" id="kpList" role="list" aria-label="Daftar kelurahan kepadatan">
                    <div class="kel-empty">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <p>Memuat data kepadatan…</p>
                    </div>
                </div>

            </div>
        </aside>
    </div>

    <div class="peta-toast" id="petaToast"></div>
</div>
@endsection

@section('scripts')
<script>
const YEARS        = @json($years);
let currentOverlay = 'rth';
let currentTahun   = @json($year ?? 2025);
let rthData        = {};
let kepadatanData  = {};
let compareData    = {};
let rekomendasiData = {};
let allKelData     = [];
let allKpData      = [];
let allRekData     = [];
let sortMode       = 'desc';
let geojsonLayer   = null;
let map;
let _compareOpen   = false;
let _loadingTimer  = null;

/* ── Toast ── */
function showToast(msg, ms = 2200) {
    const t = document.getElementById('petaToast');
    if (!t) return;
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), ms);
}

/* ── Init ── */
document.addEventListener('DOMContentLoaded', () => {
    map = L.map('map', { zoomControl: false }).setView([-6.2700, 107.0013], 12);
    L.control.zoom({ position: 'bottomright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    loadData(currentTahun);

    map.on('click', () => {
        const sb = document.getElementById('petaSidebar');
        if (sb?.classList.contains('drawer-open')) { sb.classList.remove('drawer-open'); updateFabIcon(); }
    });

    document.getElementById('selTahun').addEventListener('change', function () {
        currentTahun = parseInt(this.value);
        const lbl = document.getElementById('mapYearLabel');
        if (lbl) lbl.textContent = currentTahun;
        loadData(currentTahun);
    });

    const searchInput = document.getElementById('searchKel');
    const clearBtn    = document.getElementById('searchClear');
    if (searchInput && clearBtn) {
        searchInput.addEventListener('input', () => clearBtn.classList.toggle('visible', searchInput.value.length > 0));
    }

    // Mobile drag handle
    const handle  = document.getElementById('sidebarHandle');
    const sidebar = document.getElementById('petaSidebar');
    if (handle && sidebar) {
        let startY = 0, startH = 0, dragging = false;
        handle.addEventListener('pointerdown', e => {
            if (window.innerWidth > 768) return;
            dragging = true; startY = e.clientY; startH = sidebar.offsetHeight;
            handle.setPointerCapture(e.pointerId);
        });
        handle.addEventListener('pointermove', e => {
            if (!dragging) return;
            const delta = startY - e.clientY;
            sidebar.style.maxHeight = Math.min(Math.max(startH + delta, 80), window.innerHeight * .85) + 'px';
        });
        handle.addEventListener('pointerup', () => { dragging = false; });
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape' && _compareOpen) toggleCompare(); });
});

const normKel = s => (s ?? '').toString().toUpperCase().replace(/\s+/g, ' ').trim();

/* ── Analisis ketetanggaan spasial: dua kelurahan dianggap bertetangga
   kalau batas polygon-nya bersinggungan/bersentuhan. Pakai Turf.js
   (turf.booleanIntersects) — bukan overlay geometri (tidak ada
   ST_Intersection/ST_Difference, tidak ada unit spasial baru terbentuk),
   cuma pengecekan relasi topologis "nempel atau tidak" antar dua bentuk
   yang sudah ada, murni geometri komputasi (bukan machine learning). ── */
function computeNeighbors(geojson) {
    const neighbors = {};
    const features = Array.isArray(geojson?.features) ? geojson.features : [];
    for (let i = 0; i < features.length; i++) {
        const gidA = features[i]?.properties?.gid;
        if (gidA == null) continue;
        if (!neighbors[gidA]) neighbors[gidA] = [];
        for (let j = i + 1; j < features.length; j++) {
            const gidB = features[j]?.properties?.gid;
            if (gidB == null) continue;
            let touching = false;
            try {
                touching = turf.booleanIntersects(features[i], features[j]);
            } catch (err) {
                touching = false; // geometri tidak valid — anggap tidak bertetangga
            }
            if (touching) {
                neighbors[gidA].push(gidB);
                if (!neighbors[gidB]) neighbors[gidB] = [];
                neighbors[gidB].push(gidA);
            }
        }
    }
    return neighbors;
}

/* ── Rekomendasi: hitung skor prioritas RTH & potensi penambahan penduduk ──
   Skor "Prioritas RTH" dan "Potensi Penduduk" berbasis standar Permen PU
   No.05/PRT/M/2008 skala kelurahan (RTH ≥ 0,30 m²/jiwa DAN luas RTH ≥
   9.000 m²) — bukan dari target 20% (itu cuma agregat kota, lihat ss-prog).

   Selain skor, dihitung juga:
   1. totalTetangga / tetanggaBelumMemenuhi → dari kelurahan yang
      berbatasan langsung (dicek via Turf.js, lihat computeNeighbors),
      berapa yang juga belum memenuhi standar RTH. Dipakai di kartu RTH
      sebagai konteks kawasan, menggantikan peringkat/nomor urut.
   2. kapasitasTambahanPenduduk → perkiraan berapa jiwa lagi yang masih
      bisa ditampung tanpa membuat kelurahan itu jadi "padat" (>200
      jiwa/ha) dan tanpa membuat RTH per kapita jatuh di bawah standar. */
function computeRekomendasi(data, neighborInfo = {}) {
    const rows = Array.isArray(data) ? data.filter(d => d?.kelurahan) : [];
    if (!rows.length) return [];

    const maxKepadatan = Math.max(...rows.map(d => Number(d.kepadatan) || 0), 1);
    const STANDAR_KAPITA   = 0.30;  // m²/jiwa (Permen PU 05/2008)
    const STANDAR_LUAS     = 9000;  // m² (Permen PU 05/2008)
    const BATAS_AMAN_HA    = 200;   // jiwa/ha — ambang sebelum masuk kategori "Tinggi" (SNI 03-1733-2004: ≥201)

    const computed = rows.map(d => {
        const pct        = Number(d.persentase_rth) ?? 0;
        const kepadatan  = Number(d.kepadatan) || 0;
        const penduduk   = Number(d.penduduk) || 0;
        const luasRth    = Number(d.luas_rth) || 0; // km²
        const luasRthM2  = luasRth * 1_000_000;
        const luasWilayahKm2 = Number(d.luas_wilayah_kel ?? d.luas_wilayah) || 0;
        const luasWilayahHa  = luasWilayahKm2 * 100;

        const skorKepadatanN = (kepadatan / maxKepadatan) * 100;

        // RTH per kapita: pakai nilai dari backend kalau ada (rth_per_kapita
        // sudah dihitung PetaController), fallback hitung sendiri.
        const rthKapita = d.rth_per_kapita != null
            ? Number(d.rth_per_kapita)
            : (penduduk > 0 ? luasRthM2 / penduduk : 0);

        // Skor Prioritas RTH: defisit per kapita (50%) + kepadatan (30%) +
        // defisit luas minimum 9.000 m² (20%)
        const defisitKapita = Math.max(0, STANDAR_KAPITA - rthKapita);
        const skorDefisit   = Math.min(100, (defisitKapita / STANDAR_KAPITA) * 100);
        const defisitLuas   = Math.max(0, STANDAR_LUAS - luasRthM2);
        const skorLuas      = Math.min(100, (defisitLuas / STANDAR_LUAS) * 100);
        const skorRth = Math.min(100, (skorDefisit * 0.5) + (skorKepadatanN * 0.3) + (skorLuas * 0.2));

        // Skor Potensi Penduduk: ruang kepadatan (60%) + surplus RTH per
        // kapita di atas standar 0,30 m²/jiwa (40%), bukan surplus dari 20%.
        const skorRuang   = 100 - skorKepadatanN;
        const surplusKapita = Math.max(0, rthKapita - STANDAR_KAPITA);
        const skorSurplus   = Math.min(100, (surplusKapita / STANDAR_KAPITA) * 100);
        const skorPotensi = Math.min(100, (skorRuang * 0.6) + (skorSurplus * 0.4));

        // ── Kebutuhan tambahan RTH (m²) agar 2 syarat Permen PU terpenuhi
        //    sekaligus: RTH ≥ 0,30 m²/jiwa DAN luas RTH ≥ 9.000 m². ──
        const targetKapitaM2 = STANDAR_KAPITA * penduduk;
        const targetMinimum  = Math.max(targetKapitaM2, STANDAR_LUAS);
        const kebutuhanRthM2 = Math.max(0, targetMinimum - luasRthM2);

        // ── Kapasitas tambahan penduduk: dibatasi DUA hal sekaligus —
        //    (a) supaya kepadatan tidak lewat ambang "Tinggi" (200 jiwa/ha)
        //    (b) supaya RTH per kapita tetap ≥ 0,30 m²/jiwa setelah nambah. ──
        const kapasitasKepadatan = luasWilayahHa > 0
            ? Math.max(0, (BATAS_AMAN_HA - kepadatan) * luasWilayahHa)
            : 0;
        const pendudukMaksRth = STANDAR_KAPITA > 0 ? (luasRthM2 / STANDAR_KAPITA) : 0;
        const kapasitasRth = Math.max(0, pendudukMaksRth - penduduk);
        const kapasitasTambahanPenduduk = luasWilayahHa > 0
            ? Math.min(kapasitasKepadatan, kapasitasRth)
            : 0;

        return {
            gid: d.gid,
            kelurahan: d.kelurahan,
            kecamatan: d.kecamatan,
            persentase_rth: pct,
            kepadatan: kepadatan,
            penduduk: penduduk,
            rth_per_kapita: Math.round(rthKapita * 100) / 100,
            skorRth: Math.round(skorRth * 10) / 10,
            skorPotensi: Math.round(skorPotensi * 10) / 10,
            kebutuhanRthM2: Math.round(kebutuhanRthM2),
            kapasitasTambahanPenduduk: Math.round(kapasitasTambahanPenduduk),
            totalTetangga: neighborInfo[d.gid]?.totalTetangga ?? 0,
            tetanggaBelumMemenuhi: neighborInfo[d.gid]?.tetanggaBelumMemenuhi ?? 0,
        };
    });

    // ── Peringkat (rankRth & rankPotensi) sudah tidak dihitung lagi — kartu
    //    RTH pakai konteks tetangga (lihat buildRthReason), kartu Kepadatan/
    //    Potensi cukup deskripsi tanpa nomor urut. ──
    return computed;
}

/* ── Load data ── */
async function loadData(tahun) {
    showLoading(true);
    try {
        const [rthRes, kpRes] = await Promise.all([
            fetch(`/api/rth/${tahun}`).then(r => r.json()),
            fetch(`/api/kepadatan/${tahun}`).then(r => r.json()),
        ]);
        rthData = {};
        kepadatanData = {};
        const rthRows = Array.isArray(rthRes?.data) ? rthRes.data : [];
        const kpRows  = Array.isArray(kpRes?.data)  ? kpRes.data  : [];
        rthRows.forEach(d => { rthData[d.gid] = d; });
        kpRows.forEach(d  => { kepadatanData[d.gid] = d; });
        allKelData = rthRows; allKpData = kpRows;
        updateSummaryRth(rthRes?.summary ?? {}, tahun);
        updateSummaryKp(kpRows);

        allRekData = [];
        try {
            const overlayGeo = await fetch(`/api/overlay/${tahun}`).then(r => r.json());
            const neighborGids = computeNeighbors(overlayGeo);
            const neighborInfo = {};
            Object.keys(neighborGids).forEach(gid => {
                const list = neighborGids[gid] || [];
                const belum = list.filter(ngid => rthData[ngid]?.status_persentase !== 'Memenuhi').length;
                neighborInfo[gid] = { totalTetangga: list.length, tetanggaBelumMemenuhi: belum };
            });
            allRekData = computeRekomendasi(allKelData, neighborInfo);
        } catch (err) {
            console.error('[PETA] Gagal hitung tetangga spasial, lanjut tanpa info tetangga:', err);
            allRekData = computeRekomendasi(allKelData);
        }
        rekomendasiData = {};
        allRekData.forEach(d => { rekomendasiData[normKel(d.kelurahan)] = d; });

        renderSidebarList(allKelData);
        renderKpList(allKpData);

        await renderMap();
    } catch (err) {
        console.error('[PETA] Gagal memuat data:', err);
        setListError('kelList', 'Gagal memuat data RTH.');
        setListError('kpList',  'Gagal memuat data kepadatan.');
        showToast('Gagal memuat data. Periksa koneksi Anda.');
    } finally {
        showLoading(false);
    }
}

function setListError(id, msg) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = `<div style="padding:16px;text-align:center;color:var(--red);font-size:12px;">${msg}</div>`;
}

/* ── RTH Summary ── */
function updateSummaryRth(s, tahun) {
    const safe = (v, sfx, dec) => v != null ? Number(v).toFixed(dec ?? 2) + sfx : '-';
    animateVal('ssWilayah', safe(s.totalWilayah, ' km²'));
    animateVal('ssRth',     safe(s.totalRth, ' km²', 3));
    animateVal('ssBadge',   (allKelData.length || '-') + ' kel.');
    bumpNum('ssMemenuhi', s.memenuhi ?? 0);
    bumpNum('ssDibawah',  s.dibawah  ?? 0);
    const pct   = s.pctKota ?? 0;
    const pctEl = document.getElementById('ssPct');
    if (pctEl) { pctEl.textContent = pct ? pct.toFixed(2) + '%' : '-'; pctEl.style.color = pct < 20 ? 'var(--red)' : 'var(--green-600)'; }
    const fillPct = Math.min(100, (pct / 20) * 100);
    const fill = document.getElementById('ssProgFill');
    const valEl = document.getElementById('ssProgVal');
    const bar   = document.getElementById('ssProgBar');
    if (fill)  fill.style.width = fillPct + '%';
    if (valEl) valEl.textContent = pct ? pct.toFixed(1) + '%' : '0%';
    if (bar)   bar.setAttribute('aria-valuenow', Math.round(pct));
}

/* ── Kepadatan Summary ── */
function updateSummaryKp(rows) {
    if (!rows.length) return;
    const total    = rows.reduce((s, d) => s + (d.jumlah_penduduk ?? (d.kepadatan ?? 0) * 100 * (d.luas_wilayah ?? 0)), 0);
    const luas     = rows.reduce((s, d) => s + (d.luas_wilayah ?? 0), 0);
    // total (jiwa) / luas (km²) = jiwa/km² → ÷100 supaya konsisten jiwa/ha
    // dengan d.kepadatan per kelurahan (fallback saat luas kosong sudah ha).
    const rataRata = luas ? (total / luas / 100) : (rows.reduce((s, d) => s + (d.kepadatan ?? 0), 0) / rows.length);
    const terpadat = [...rows].sort((a, b) => (b.kepadatan ?? 0) - (a.kepadatan ?? 0))[0];
    animateVal('kpTotalPenduduk', total    ? total.toLocaleString('id-ID') + ' jiwa' : '-');
    animateVal('kpLuasWilayah',   luas     ? luas.toFixed(2) + ' km²' : '-');
    animateVal('kpRataRata',      rataRata ? Math.round(rataRata).toLocaleString('id-ID') + ' jiwa/ha' : '-');
    animateVal('kpTerpadat',      terpadat ? terpadat.kelurahan : '-');
    animateVal('kpBadge',         rows.length + ' kel.');
    const counts = { sp: 0, t: 0, s: 0, r: 0 };
    rows.forEach(d => {
        const v = d.kepadatan ?? 0;
        if (v > 400)        counts.sp++;
        else if (v >= 201) counts.t++;
        else if (v >= 151) counts.s++;
        else counts.r++;
    });
    const n = rows.length || 1;
    animateVal('kpDistLabel', rows.length + ' kelurahan');
    setDistBar('kpBarSP','kpCountSP',counts.sp,n);
    setDistBar('kpBarP', 'kpCountP', counts.t, n);
    setDistBar('kpBarS', 'kpCountS', counts.s, n);
    setDistBar('kpBarR', 'kpCountR', counts.r, n);
}

function setDistBar(barId, countId, count, total) {
    const bar = document.getElementById(barId);
    const cnt = document.getElementById(countId);
    if (bar) bar.style.width = ((count / total) * 100).toFixed(1) + '%';
    if (cnt) cnt.textContent = count;
}

/* ── Animation helpers ── */
function animateVal(id, val) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.opacity = '.4'; el.style.transition = 'opacity .3s';
    setTimeout(() => { el.textContent = val; el.style.opacity = '1'; }, 80);
}

function bumpNum(id, val) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = val;
    el.classList.remove('bump'); void el.offsetWidth; el.classList.add('bump');
    setTimeout(() => el.classList.remove('bump'), 400);
}

/* ── RTH badge status helper ──
   Status per-kelurahan sekarang standar Permen PU 05/2008 (0,30 m²/jiwa &
   9.000 m²) — dihitung backend (apiRth), bukan threshold %-luas di sini. */
function pctBadgeInfo(statusLabel) {
    return statusLabel === 'Memenuhi'
        ? { cls: 'ski-pct-ideal',  label: 'Memenuhi' }
        : { cls: 'ski-pct-kritis', label: 'Belum Memenuhi' };
}

/* ── Narasi rekomendasi: alasan logis, bukan cuma angka mentah ──
   Standar: Permen PU No. 05/PRT/M/2008 (RTH skala kelurahan) —
   RTH per kapita ≥ 0,30 m²/jiwa DAN luas RTH ≥ 9.000 m². Konsisten
   dipakai di 4 tempat (sidebar RTH, sidebar Kepadatan, popup RTH,
   popup Kepadatan) supaya alasannya tidak beda-beda. */
/* Label kepadatan mengikuti kelas SNI 03-1733-2004 yang sama dengan
   kpCategory() — Rendah / Sedang / Tinggi / Sangat Padat (jiwa/ha). */
function densityLabel(kp) {
    if (kp > 400)  return 'kepadatan sangat tinggi';
    if (kp >= 201) return 'kepadatan tinggi';
    if (kp >= 151) return 'kepadatan sedang';
    return 'kepadatan masih renggang';
}

function buildRthReason(rthPerKapita, penduduk, kepadatan, extra = {}) {
    const { tetanggaBelumMemenuhi = 0, totalTetangga = 0 } = extra;
    const gap = rthPerKapita - 0.30;
    const kapitaDesc = gap < 0
        ? `Ruang hijau di sini baru sekitar ${rthPerKapita.toFixed(2)} m² untuk setiap warga, padahal standar minimalnya 0,30 m²/warga`
        : `Ruang hijau di sini sudah sekitar ${rthPerKapita.toFixed(2)} m² per warga, di atas standar minimal 0,30 m²`;
    const densFlag = kepadatan >= 151 ? `, ditambah lagi penduduknya ${densityLabel(kepadatan)}` : '';
    // ── Konteks tetangga: dari kelurahan yang berbatasan langsung (dicek
    //    via Turf.js, lihat computeNeighbors), berapa yang juga belum
    //    memenuhi standar. Ini pengganti "peringkat" — lebih mudah
    //    dijelaskan karena logikanya cuma "berbatasan atau tidak", bukan
    //    formula skor berlapis. ──
    const neighborTxt = (totalTetangga > 0 && tetanggaBelumMemenuhi > 0)
        ? `${tetanggaBelumMemenuhi} dari ${totalTetangga} kelurahan tetangga yang berbatasan langsung juga belum memenuhi standar RTH — kawasan ini secara umum kekurangan ruang hijau. `
        : '';
    return `${neighborTxt}${kapitaDesc}${densFlag}.`;
}

function buildPotensiReason(rthPerKapita, penduduk, kepadatan, extra = {}) {
    const { kapasitasTambahanPenduduk = 0 } = extra;
    const densDesc = kepadatan < 151
        ? `Kepadatan penduduknya masih tergolong ${densityLabel(kepadatan)} (${kepadatan.toLocaleString('id-ID')} jiwa/ha)`
        : `Kepadatan penduduknya ${densityLabel(kepadatan)} (${kepadatan.toLocaleString('id-ID')} jiwa/ha)`;
    // ── Label kapasitas dibuat kualitatif (bukan angka jiwa persis), diukur
    //    relatif terhadap jumlah penduduk saat ini supaya adil buat
    //    kelurahan kecil maupun besar. ──
    const rasioKapasitas = penduduk > 0 ? kapasitasTambahanPenduduk / penduduk : 0;
    let capTxt;
    if (kapasitasTambahanPenduduk <= 0) {
        capTxt = ' Kapasitas tambahan penduduk di sini sudah sangat terbatas.';
    } else if (rasioKapasitas >= 0.5) {
        capTxt = ' Wilayah ini masih bisa menampung banyak tambahan penduduk.';
    } else if (rasioKapasitas >= 0.15) {
        capTxt = ' Wilayah ini masih bisa menampung tambahan penduduk dalam jumlah sedang.';
    } else {
        capTxt = ' Kapasitas tambahan penduduk di sini sudah cukup terbatas.';
    }
    return `${densDesc}.${capTxt}`;
}

/* ── RTH list ── */
function renderSidebarList(data) {
    const list = document.getElementById('kelList');
    if (!list) return;
    const safeData = Array.isArray(data) ? data : [];
    const sorted   = [...safeData].sort((a, b) => {
        if (sortMode === 'asc')   return (a.persentase_rth ?? 0) - (b.persentase_rth ?? 0);
        if (sortMode === 'alpha') return (a.kelurahan ?? '').localeCompare(b.kelurahan ?? '');
        return (b.persentase_rth ?? 0) - (a.persentase_rth ?? 0);
    });
    if (!sorted.length) { list.innerHTML = '<div class="kel-empty"><p>Tidak ada data kelurahan.</p></div>'; return; }
    list.innerHTML = sorted.map(d => {
        if (!d?.kelurahan) return '';
        const pct = d.luas_wilayah_kel > 0
            ? (Number(d.luas_rth) / Number(d.luas_wilayah_kel)) * 100
            : 0;
        const pctTxt = d.persentase_rth != null ? pct.toFixed(4) + '%' : '-';
        const memenuhi = d.status_persentase === 'Memenuhi';
        const color  = memenuhi ? '#16a34a' : '#dc2626';
        const barW   = Math.min(100, pct * 2).toFixed(1);
        const badge  = pctBadgeInfo(d.status_persentase);
        const luasKel = d.luas_wilayah_kel ?? d.luas_wilayah ?? null;
        const sub    = [d.luas_rth != null ? Number(d.luas_rth).toFixed(3) + ' km² RTH' : null, luasKel != null ? Number(luasKel).toFixed(2) + ' km² wilayah' : null].filter(Boolean).join(' · ');

        // Rekomendasi RTH ditempel di detail tiap kelurahan tab ini —
        // rekomendasi potensi penduduk ada di tab Kepadatan (renderKpList).
        const rek = rekomendasiData[normKel(d.kelurahan)];
        let rekHtml = '';
        let rankBadge = '';
        if (rek) {
            const reasonRth = buildRthReason(rek.rth_per_kapita, rek.penduduk, rek.kepadatan, {
                tetanggaBelumMemenuhi: rek.tetanggaBelumMemenuhi, totalTetangga: rek.totalTetangga,
            });
            rekHtml = `<div class="ski-rek"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg><span>${reasonRth}</span></div>`;
            if (rek.totalTetangga > 0 && rek.tetanggaBelumMemenuhi > 0) {
                rankBadge = `<span class="ski-rank-badge" title="Kelurahan berbatasan langsung yang juga belum memenuhi standar RTH">${rek.tetanggaBelumMemenuhi}/${rek.totalTetangga} tetangga belum memenuhi</span>`;
            }
        }

        return `<div class="sidebar-kel-item" role="listitem" data-gid="${d.gid}" onclick="focusKel(${d.gid})">
            <div class="ski-top"><div style="min-width:0;"><div class="ski-name">${d.kelurahan}</div>${sub ? `<div class="ski-sub">${sub}</div>` : ''}</div><div style="display:flex;flex-direction:column;align-items:flex-end;gap:3px;"><span class="ski-pct-badge ${badge.cls}">${pctTxt} · ${badge.label}</span>${rankBadge}</div></div>
            <div class="ski-bar"><div class="ski-bar-fill" style="width:${barW}%;background:${color}"></div></div>
            ${rekHtml}
        </div>`;
    }).join('');
}

/* ── Kepadatan list ── */
function renderKpList(data) {
    const list = document.getElementById('kpList');
    if (!list) return;
    const sorted = [...(Array.isArray(data) ? data : [])].sort((a, b) => (b.kepadatan ?? 0) - (a.kepadatan ?? 0));
    if (!sorted.length) { list.innerHTML = '<div class="kel-empty"><p>Tidak ada data kepadatan.</p></div>'; return; }
    const max = sorted[0]?.kepadatan ?? 1;
    list.innerHTML = sorted.map(d => {
        if (!d?.kelurahan) return '';
        const kp    = d.kepadatan ?? 0;
        const kpTxt = kp ? kp.toLocaleString('id-ID') + ' jiwa/ha' : '-';
        const color = kepadatanColor(kp);
        const barW  = ((kp / max) * 100).toFixed(1);
        const cat   = kpCategory(kp);
        const penduduk = d.jumlah_penduduk ? d.jumlah_penduduk.toLocaleString('id-ID') + ' jiwa' : null;
        const sub   = [penduduk, d.kecamatan ? 'Kec. ' + d.kecamatan : null].filter(Boolean).join(' · ');

        // Rekomendasi potensi tambah penduduk ditempel di tab ini
        // rekomendasi prioritas RTH ada di tab Kelurahan (renderSidebarList).
        const rek = rekomendasiData[normKel(d.kelurahan)];
        let rekHtml = '';
        let rankBadge = '';
        if (rek) {
            const reasonPotensi = buildPotensiReason(rek.rth_per_kapita, rek.penduduk, rek.kepadatan, {
                kapasitasTambahanPenduduk: rek.kapasitasTambahanPenduduk,
            });
            rekHtml = `<div class="ski-rek"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg><span>${reasonPotensi}</span></div>`;
        }

        return `<div class="kp-kel-item" role="listitem" data-gid="${d.gid}" onclick="focusKel(${d.gid})">
            <div class="kpi-top"><div style="min-width:0;"><div class="kpi-name">${d.kelurahan}</div>${sub ? `<div class="kpi-cat">${sub}</div>` : ''}</div><div style="text-align:right;flex-shrink:0;"><div class="kpi-val" style="color:${color}">${kpTxt}</div><div class="kpi-cat" style="color:${color}">${cat}</div>${rankBadge ? `<div style="margin-top:3px;">${rankBadge}</div>` : ''}</div></div>
            <div class="kpi-bar"><div class="kpi-bar-fill" style="width:${barW}%;background:${color}"></div></div>
            ${rekHtml}
        </div>`;
    }).join('');
}

function highlightSidebarItem(gid) {
    const key = String(gid);
    document.querySelectorAll('.sidebar-kel-item, .kp-kel-item, .cp-list-item').forEach(el => {
        el.classList.toggle('highlighted', el.dataset.gid === key);
    });
    // scroll ke item yang match di panel yang lagi kelihatan aja
    document.querySelectorAll('.sidebar-kel-item.highlighted, .kp-kel-item.highlighted, .cp-list-item.highlighted').forEach(el => {
        if (el.offsetParent !== null) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
}

/* ── Focus kelurahan (dipanggil dari sidebar, panel compare, maupun klik peta) ── */
function focusKel(gid) {
    highlightSidebarItem(gid);
    if (!geojsonLayer) return;
    geojsonLayer.eachLayer(layer => {
        if (String(layer.feature?.properties?.gid) === String(gid)) {
            map.fitBounds(layer.getBounds(), { padding: [60, 60] });
            layer.openPopup();
        }
    });
}

/* ── Overlay switch ── */
function setOverlay(type) {
    currentOverlay = type;

    // Mobile/map legend toggle
    const legRth = document.getElementById('mobileLegendRth');
    const legKp  = document.getElementById('mobileLegendKepadatan');
    const legTransisi = document.getElementById('mobileLegendTransisi');
    if (legTransisi) legTransisi.style.display = 'none';
    if (legRth && legKp) {
        const showRth = type === 'rth';
        legRth.style.display = showRth ? '' : 'none';
        legKp.style.display  = showRth ? 'none' : '';
    }

    const btnRth = document.getElementById('btnRth');
    const btnKp  = document.getElementById('btnKepadatan');
    if (btnRth) { btnRth.classList.toggle('active', type === 'rth'); btnRth.setAttribute('aria-pressed', type === 'rth' ? 'true' : 'false'); }
    if (btnKp)  { btnKp.classList.toggle('active', type === 'kepadatan'); btnKp.setAttribute('aria-pressed', type === 'kepadatan' ? 'true' : 'false'); }

    const fg = document.getElementById('filterRthGroup');
    if (fg) fg.style.display = type === 'rth' ? '' : 'none';
    const fgDivider = document.getElementById('filterRthDivider');
    if (fgDivider) fgDivider.style.display = type === 'rth' ? '' : 'none';

    const pRth = document.getElementById('sidebarRth');
    const pKp  = document.getElementById('sidebarKepadatan');
    if (type === 'kepadatan') {
        pRth?.classList.remove('sidebar-panel--active'); pRth?.setAttribute('aria-hidden', 'true');
        pKp?.classList.add('sidebar-panel--active');    pKp?.removeAttribute('aria-hidden');
        document.body.classList.add('mode-kepadatan');
    } else {
        pKp?.classList.remove('sidebar-panel--active');  pKp?.setAttribute('aria-hidden', 'true');
        pRth?.classList.add('sidebar-panel--active');   pRth?.removeAttribute('aria-hidden');
        document.body.classList.remove('mode-kepadatan');
    }

    renderMap();
}

/* ── Filter / Search / Sort ── */
function applyFilter() {
    const f = document.getElementById('selFilter').value;
    let data = allKelData;
    if (f === 'below') data = allKelData.filter(d => d.status_persentase !== 'Memenuhi');
    if (f === 'above') data = allKelData.filter(d => d.status_persentase === 'Memenuhi');
    renderSidebarList(data);
}

function filterSearch(val) {
    const q        = (val ?? '').toString().trim().toLowerCase();
    const selector = currentOverlay === 'kepadatan' ? '.kp-kel-item'  : '.sidebar-kel-item';
    const nameClass= currentOverlay === 'kepadatan' ? '.kpi-name'      : '.ski-name';
    document.querySelectorAll(selector).forEach(el => {
        el.style.display = !q || (el.querySelector(nameClass)?.textContent ?? '').toLowerCase().includes(q) ? '' : 'none';
    });
}

function clearSearch() {
    const input = document.getElementById('searchKel');
    const btn   = document.getElementById('searchClear');
    if (input) { input.value = ''; input.focus(); }
    if (btn)   btn.classList.remove('visible');
    filterSearch('');
}

const SORT_MODES  = ['desc', 'asc', 'alpha'];
const SORT_LABELS = ['Terbesar →', 'Terkecil →', 'A-Z →'];

function cycleSortMode() {
    const idx  = SORT_MODES.indexOf(sortMode);
    sortMode   = SORT_MODES[(idx + 1) % SORT_MODES.length];
    const lbl  = document.getElementById('slSortLabel');
    if (lbl) lbl.textContent = SORT_LABELS[(idx + 1) % SORT_LABELS.length];
    renderSidebarList(allKelData);
}

function toggleSidebarSection(el, listId = 'kelList') {
    const list      = document.getElementById(listId);
    if (!list) return;
    const collapsed = list.style.display === 'none';
    list.style.display = collapsed ? '' : 'none';
    el.classList.toggle('collapsed', !collapsed);
    el.setAttribute('aria-expanded', collapsed ? 'true' : 'false');
}

function toggleRekDisclaimer(btn) {
    const body = document.getElementById(btn.getAttribute('aria-controls'));
    if (!body) return;
    const open = !body.classList.contains('open');
    body.classList.toggle('open', open);
    btn.setAttribute('aria-expanded', String(open));
}

/* ── Compare panel ── */
function toggleCompare() {
    _compareOpen = !_compareOpen;
    const panel = document.getElementById('comparePanel');
    const btn   = document.getElementById('btnCompare');
    if (!panel) return;
    panel.classList.toggle('open', _compareOpen);
    panel.setAttribute('aria-hidden', String(!_compareOpen));
    if (btn) { btn.setAttribute('aria-expanded', String(_compareOpen)); btn.classList.toggle('is-compare-open', _compareOpen); }
    if (_compareOpen) panel.querySelector('.cp-close')?.focus();
    // Mode "Peta Transisi Status RTH" cuma relevan selama panel Bandingkan
    // kebuka — begitu ditutup, balik ke tampilan status RTH normal.
    if (!_compareOpen && currentOverlay === 'transisi') setOverlay('rth');
}

/* ── Aktifkan mode "Peta Transisi Status RTH" di peta utama, dipicu dari
   panel Bandingkan. Sengaja tidak punya tombol toggle sendiri di
   "Overlay Peta" — mode ini aktif selama panel Bandingkan menampilkan
   hasil, dan otomatis balik ke RTH begitu panel ditutup (lihat
   toggleCompare()). ── */
function activateTransisiOverlay() {
    currentOverlay = 'transisi';

    const legRth       = document.getElementById('mobileLegendRth');
    const legKp         = document.getElementById('mobileLegendKepadatan');
    const legTransisi   = document.getElementById('mobileLegendTransisi');
    if (legRth) legRth.style.display = 'none';
    if (legKp)  legKp.style.display  = 'none';
    if (legTransisi) legTransisi.style.display = '';

    const btnRth = document.getElementById('btnRth');
    const btnKp  = document.getElementById('btnKepadatan');
    if (btnRth) { btnRth.classList.remove('active'); btnRth.setAttribute('aria-pressed', 'false'); }
    if (btnKp)  { btnKp.classList.remove('active');  btnKp.setAttribute('aria-pressed', 'false'); }

    renderMap();
}

async function handleLoadCompare() {
    const y1  = document.getElementById('cpYear1')?.value;
    const y2  = document.getElementById('cpYear2')?.value;
    const btn = document.getElementById('cpGoBtn');
    if (y1 === y2) { shakeEl('.cp-year-row'); showToast('Pilih dua tahun yang berbeda.'); return; }

    if (btn) { btn.classList.add('loading'); btn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg> Memuat…'; }
    ['cpSummary','cpTotals','cpListHeaderWrap','cpList'].forEach(id => { const el = document.getElementById(id); if (el) el.style.display = 'none'; });
    const hint = document.getElementById('cpHint');
    if (hint) hint.style.display = 'none';

    try {
        const res = await fetch(`/api/compare/${y1}/${y2}`).then(r => r.json());
        setChipAnim('cpTurun', res.turun ?? 0);
        setChipAnim('cpTetap', res.tetap ?? 0);
        setChipAnim('cpNaik',  res.naik  ?? 0);

        // ── Peta Transisi Status RTH: simpan data buat pewarnaan peta,
        //    lalu aktifkan mode transisi (bukan overlay geometri — lihat
        //    catatan di legenda/popup). Geometri tetap pakai layer yang
        //    sudah dimuat renderMap(), cuma dicocokkan lewat gid. ──
        compareData = {};
        (res.data || []).forEach(d => { compareData[d.gid] = d; });
        activateTransisiOverlay();
        const sum = document.getElementById('cpSummary');
        if (sum) sum.style.display = 'grid';
        const tot = document.getElementById('cpTotals');
        const y1l = document.getElementById('cpY1Label');
        const y2l = document.getElementById('cpY2Label');
        const t1  = document.getElementById('cpTot1');
        const t2  = document.getElementById('cpTot2');
        const sel = document.getElementById('cpSelisih');
        if (y1l) y1l.textContent = y1;
        if (y2l) y2l.textContent = y2;
        if (t1)  t1.textContent  = (res.total_tahun1 ?? 0).toFixed(4) + ' km²';
        if (t2)  t2.textContent  = (res.total_tahun2 ?? 0).toFixed(4) + ' km²';
        if (sel) { const diff = res.selisih ?? 0; sel.textContent = (diff >= 0 ? '+' : '') + diff.toFixed(4) + ' km²'; sel.style.color = diff >= 0 ? 'var(--green-600)' : 'var(--red)'; }
        if (tot) tot.style.display = 'block';
        const lh  = document.getElementById('cpListHeaderWrap');
        const cl  = document.getElementById('cpList');
        if (lh) lh.style.display = 'flex';
        if (cl) {
            cl.style.display = 'block';
            cl.innerHTML = (res.data || []).map((d, i) => {
                const tc   = d.trend === 'naik' ? 'trend-up' : d.trend === 'turun' ? 'trend-down' : 'trend-same';
                const sign = (d.diff_pct ?? 0) >= 0 ? '+' : '';
                const icon = d.trend === 'naik' ? '↑' : d.trend === 'turun' ? '↓' : '→';
                return `<div class="cp-list-item" data-gid="${d.gid}" onclick="focusKel(${d.gid})"><span style="font-weight:600;">${icon} ${d.kelurahan}</span><span class="${tc}" style="font-weight:700;">${sign}${(d.diff_pct ?? 0).toFixed(4)}%</span></div>`;
            }).join('');
        }
    } catch (err) {
        console.error('[PETA] Gagal compare:', err);
        showToast('Gagal memuat data perbandingan.');
        if (hint) hint.style.display = '';
    } finally {
        if (btn) { btn.classList.remove('loading'); btn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg> Tampilkan'; }
    }
}

function setChipAnim(id, val) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = val;
    el.style.transition = 'transform .3s cubic-bezier(0.34,1.56,0.64,1)';
    el.style.transform  = 'scale(1.35)';
    setTimeout(() => { el.style.transform = ''; }, 50);
}

/* ── Mobile sidebar ── */
function toggleMobileSidebar() {
    document.getElementById('petaSidebar')?.classList.toggle('drawer-open');
    updateFabIcon();
}

function updateFabIcon() {
    const isOpen = document.getElementById('petaSidebar')?.classList.contains('drawer-open');
    const o = document.getElementById('fabIconOpen');
    const c = document.getElementById('fabIconClose');
    if (o) o.style.display = isOpen ? 'none'  : 'block';
    if (c) c.style.display = isOpen ? 'block' : 'none';
    document.getElementById('sidebarToggleBtn')?.classList.toggle('fab-drawer-open', !!isOpen);
}

/* ── Loading ── */
function showLoading(show) {
    const el = document.getElementById('mapLoading');
    if (!el) return;
    if (show) { clearTimeout(_loadingTimer); el.classList.remove('hidden', 'fully-hidden'); }
    else { el.classList.add('hidden'); _loadingTimer = setTimeout(() => el.classList.add('fully-hidden'), 300); }
}

/* ── Shake ── */
function shakeEl(sel) {
    const el = document.querySelector(sel);
    if (!el) return;
    el.style.animation = 'none'; void el.offsetWidth;
    el.style.animation = 'shake .4s ease';
    setTimeout(() => { el.style.animation = ''; }, 450);
}

/* ── Render map ── */
async function renderMap() {
    if (geojsonLayer) { map.removeLayer(geojsonLayer); geojsonLayer = null; }
    let geojson;
    try {
        geojson = await fetch(`/api/overlay/${currentTahun}`).then(r => r.json());
    } catch (err) { console.error('[PETA] Gagal overlay:', err); return; }
    if (!geojson?.features?.length) return;

    geojsonLayer = L.geoJSON(geojson, {
        style(feature) {
            const gid = feature.properties.gid;
            const kec = normKel(feature.properties.kecamatan);
            let recRth = rthData[gid];
            let recKp  = kepadatanData[gid];
            if (!recRth) recRth = Object.values(rthData).find(x => normKel(x.kecamatan) === kec);
            if (!recKp)  recKp  = Object.values(kepadatanData).find(x => normKel(x.kelurahan) === kec);
            let fillColor = '#d1d5db';
            if (currentOverlay === 'transisi') {
                const cmp = compareData[gid];
                if (cmp) fillColor = transisiColor(cmp.transisi);
            } else if (currentOverlay === 'rth') {
                // Status per kelurahan sesuai Permen PU 05/2008, bukan lagi
                // dari persentase luas RTH ≥20% (itu cuma target agregat kota).
                const memenuhi = recRth?.status_persentase != null
                    ? recRth.status_persentase === 'Memenuhi'
                    : !!feature.properties.memenuhi_standar_kelurahan;

                fillColor = rthColor(memenuhi);
            } else {
                const kv = recKp?.kepadatan ?? feature.properties.kepadatan;
                if (kv != null && kv !== '') fillColor = kepadatanColor(Number(kv));
            }
            return { fillColor, weight: 2, opacity: 1, color: '#1f2937', fillOpacity: 0.55 };
        },
        onEachFeature(feature, layer) {
            const gid     = feature.properties.gid;
            const kelKey  = normKel(feature.properties.kelurahan);
            const rth     = rthData[gid];
            console.log("RTH DATA:", rth);
            const kp      = kepadatanData[gid];
            const kelName = feature.properties.kelurahan || kelKey;
            const kecName = feature.properties.kecamatan || '';
            let html = '';
            if (currentOverlay === 'transisi') {
                const cmp = compareData[gid];
                if (cmp) {
                    const trLabel = transisiLabel(cmp.transisi);
                    const trColor = transisiColor(cmp.transisi);
                    const diffTxt = (cmp.diff_pct > 0 ? '+' : '') + Number(cmp.diff_pct).toFixed(4) + '%';
                    html = `<div class="popup-inner"><div class="popup-title">${kelName}${kecName?`<span class="popup-kec">Kec. ${kecName}</span>`:''}</div>
                        <div class="popup-row"><span class="pk">Status Tahun Awal</span><span class="pv">${cmp.status_tahun1}</span></div>
                        <div class="popup-row"><span class="pk">Status Tahun Akhir</span><span class="pv">${cmp.status_tahun2}</span></div>
                        <div class="popup-row"><span class="pk">Luas RTH Awal → Akhir</span><span class="pv">${Number(cmp.luas_tahun1).toFixed(4)} → ${Number(cmp.luas_tahun2).toFixed(4)} km²</span></div>
                        <div class="popup-row"><span class="pk">Selisih % RTH</span><span class="pv">${diffTxt}</span></div>
                        <div class="popup-badge-wrap"><span class="popup-badge" style="background:${trColor}1a;color:${trColor};border-color:${trColor}44;">${trLabel}</span></div>
                        <div class="popup-row" style="margin-top:6px;"><span class="pv" style="font-size:10px;color:var(--text-light);line-height:1.5;">Perbandingan status kepatuhan Permen PU 05/2008 antar dua tahun untuk kelurahan ini — bukan hasil overlay geometri.</span></div>
                        </div>`;
                } else {
                    html = `<div class="popup-inner"><div class="popup-title">${kelName}</div><div class="popup-row"><span class="pv" style="font-size:11px;color:var(--text-light);">Tidak ada data perbandingan untuk kelurahan ini.</span></div></div>`;
                }
            } else if (currentOverlay === 'rth') {
                const luasRth = rth?.luas_rth ?? 0;
                const luasWil = rth?.luas_wilayah_kel ?? rth?.luas_wilayah ?? 0;
                const pctNum = luasWil > 0
                    ? (Number(luasRth) / Number(luasWil)) * 100
                    : 0;
                // Status Memenuhi/Belum Memenuhi ikut standar Permen PU 05/2008
                // dari backend (rth.status_persentase), bukan pctNum >= 20.
                const statusOk = rth?.status_persentase != null
                    ? rth.status_persentase === 'Memenuhi'
                    : !!feature.properties.memenuhi_standar_kelurahan;
                const badgeClass = statusOk ? 'badge-ok' : 'badge-no';
                const badgeLabel = rth?.status_persentase ?? (statusOk ? 'Memenuhi' : 'Belum Memenuhi');
                const rthPerKapitaTxt = rth?.rth_per_kapita != null
                    ? Number(rth.rth_per_kapita).toFixed(2) + ' m²/jiwa'
                    : (feature.properties.rth_per_kapita != null ? Number(feature.properties.rth_per_kapita).toFixed(2) + ' m²/jiwa' : '-');

                // Rekomendasi RTH ditampilkan di popup mode RTH — rekomendasi
                // potensi penduduk ada di popup mode Kepadatan (branch else).
                const rek = rekomendasiData[kelKey];
                let rekRow = '';
                if (rek) {
                    const reasonRthPopup = buildRthReason(rek.rth_per_kapita, rek.penduduk, rek.kepadatan, {
                        tetanggaBelumMemenuhi: rek.tetanggaBelumMemenuhi, totalTetangga: rek.totalTetangga,
                    });
                    rekRow = `<div class="popup-row" style="margin-top:6px;padding-top:6px;border-top:1px dashed var(--border);"><span class="pk" style="color:var(--text-mid);font-weight:700;display:flex;align-items:center;gap:4px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>Rekomendasi</span></div>
                    <div class="popup-row"><span class="pv" style="font-size:10px;color:var(--text-light);line-height:1.5;">${reasonRthPopup}</span></div>`;
                }

                html = `<div class="popup-inner"><div class="popup-title">${kelName}${kecName?`<span class="popup-kec">Kec. ${kecName}</span>`:''}</div>
                    <div class="popup-row"><span class="pk">Luas RTH</span><span class="pv">${luasRth!=null?Number(luasRth).toFixed(4)+' km²':'-'}</span></div>
                    <div class="popup-row"><span class="pk">Luas Wilayah</span><span class="pv">${luasWil!=null?Number(luasWil).toFixed(2)+' km²':'-'}</span></div>
                    <div class="popup-row"><span class="pk">Persentase RTH</span><span class="pv" style="font-size:13px;font-weight:800;">${pctNum.toFixed(4)}%</span></div>
                    <div class="popup-row"><span class="pk">RTH per Kapita</span><span class="pv">${rthPerKapitaTxt}</span></div>
                    <div class="popup-row"><span class="pk">Tahun Data</span><span class="pv">${currentTahun}</span></div>
                    <div class="popup-badge-wrap">${badgeLabel?`<span class="popup-badge ${badgeClass}">${badgeLabel}</span>`:''}</div>
                    ${rekRow}</div>`;
            } else {
                const kpVal   = kp?.kepadatan ?? feature.properties.kepadatan;
                const kpNum   = kpVal != null ? Number(kpVal) : null;
                const penduduk = kp?.jumlah_penduduk ?? feature.properties.jumlah_penduduk ?? null;
                const luasWil  = kp?.luas_wilayah ?? feature.properties.luas_kelurahan ?? null;
                const luasRthKp = rth?.luas_rth ?? feature.properties.luas_rth ?? null;
                const cat      = kpNum != null ? kpCategory(kpNum) : '-';
                let catClass   = 'badge-sedang';
                if (kpNum != null) {
                    if (kpNum > 400)        catClass = 'badge-sangat';
                    else if (kpNum >= 201) catClass = 'badge-no';
                    else if (kpNum >= 151) catClass = 'badge-sedang';
                    else                   catClass = 'badge-rendah';
                }

                // Rekomendasi potensi tambah penduduk khusus popup mode Kepadatan.
                const rekKp = rekomendasiData[kelKey];
                let rekKpRow = '';
                if (rekKp) {
                    const reasonPotensiPopup = buildPotensiReason(rekKp.rth_per_kapita, rekKp.penduduk, rekKp.kepadatan, {
                        kapasitasTambahanPenduduk: rekKp.kapasitasTambahanPenduduk,
                    });
                    rekKpRow = `<div class="popup-row" style="margin-top:6px;padding-top:6px;border-top:1px dashed var(--border);"><span class="pk" style="color:var(--text-mid);font-weight:700;display:flex;align-items:center;gap:4px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>Rekomendasi</span></div>
                    <div class="popup-row"><span class="pv" style="font-size:10px;color:var(--text-light);line-height:1.5;">${reasonPotensiPopup}</span></div>`;
                }

                html = `<div class="popup-inner"><div class="popup-title">${kelName}${kecName?`<span class="popup-kec">Kec. ${kecName}</span>`:''}</div>
                    <div class="popup-row"><span class="pk">Kepadatan</span><span class="pv" style="font-size:13px;font-weight:800;">${kpNum!=null?kpNum.toLocaleString('id-ID')+' jiwa/ha':'-'}</span></div>
                    <div class="popup-row"><span class="pk">Jumlah Penduduk</span><span class="pv">${penduduk!=null?Number(penduduk).toLocaleString('id-ID')+' jiwa':'-'}</span></div>
                    <div class="popup-row"><span class="pk">Luas Wilayah</span><span class="pv">${luasWil!=null?Number(luasWil).toFixed(2)+' km²':'-'}</span></div>
                    <div class="popup-row"><span class="pk">Luas RTH</span><span class="pv">${luasRthKp!=null?Number(luasRthKp).toFixed(4)+' km²':'-'}</span></div>
                    <div class="popup-row"><span class="pk">Tahun Data</span><span class="pv">${currentTahun}</span></div>
                    <div class="popup-badge-wrap"><span class="popup-badge ${catClass}">${cat}</span></div>
                    ${rekKpRow}</div>`;
            }
            layer.bindTooltip(html, { sticky: true, opacity: 1, className: 'peta-tooltip' });
            const center = layer.getBounds().getCenter();
            L.marker(center, {
                interactive: false,
                icon: L.divIcon({
                    className: 'label-kelurahan',
                    html: kelName
                })
            }).addTo(map);
            layer.bindPopup(html, { maxWidth: Math.min(240, window.innerWidth - 48) });
            layer.on({
                mouseover(e) { e.target.setStyle({ weight: 3, fillOpacity: 0.9, color: '#111827' }); e.target.openTooltip(); },
                mouseout(e)  { try { geojsonLayer.resetStyle(e.target); } catch(_) {} e.target.closeTooltip(); },
                click(e) {
                    map.fitBounds(e.target.getBounds(), { padding: [40, 40] });
                    e.target.openPopup();
                    highlightSidebarItem(gid);
                    if (window.innerWidth <= 768) { document.getElementById('petaSidebar')?.classList.add('drawer-open'); updateFabIcon(); }
                }
            });
        }
    }).addTo(map);
    try { map.fitBounds(geojsonLayer.getBounds()); } catch(_) {}
}

/* ── Reset ── */
function resetMapView() {
    map.flyTo([-6.2700, 107.0013], 12, { duration: 0.6, easeLinearity: 0.4 });
    document.querySelectorAll('.sidebar-kel-item.highlighted, .kp-kel-item.highlighted, .cp-list-item.highlighted').forEach(el => el.classList.remove('highlighted'));
    showToast('Tampilan peta direset');
}

/* ── Color helpers ── */
// Warna kelurahan mengikuti status Permen PU 05/2008 (0,30 m²/jiwa &
// ≥9.000 m² RTH), bukan lagi persentase luas ≥20% (itu hanya agregat kota).
function rthColor(memenuhi) {
    return memenuhi ? '#166534' : '#dc2626';
}
/* ── Klasifikasi kepadatan: SNI 03-1733-2004 (Tata Cara Perencanaan
   Lingkungan Perumahan di Perkotaan) — satuan jiwa/ha (backend sudah
   mengonversi dari jiwa/km² ÷100, lihat PetaController):
   Rendah <150 · Sedang 151–200 · Tinggi 201–400 · Sangat Padat >400 */
function kepadatanColor(kp) {
    if (kp > 400) return '#7f1d1d';
    if (kp >= 201) return '#dc2626';
    if (kp >= 151) return '#f97316';
    return '#86efac';
}
function kpCategory(kp) {
    if (kp > 400)  return 'Sangat Padat';
    if (kp >= 201) return 'Tinggi';
    if (kp >= 151) return 'Sedang';
    return 'Rendah';
}

/* ── Peta Transisi Status RTH: komparasi status Memenuhi/Belum Memenuhi
   (Permen PU 05/2008) antar dua tahun untuk kelurahan yang sama.
   Ini BUKAN overlay spasial (tidak ada ST_Intersection/ST_Difference,
   tidak ada geometri baru terbentuk) — hanya perbandingan status/atribut
   yang divisualisasikan di atas geometri kelurahan yang sudah ada. ── */
function transisiColor(transisi) {
    switch (transisi) {
        case 'konsisten_memenuhi': return '#166534';
        case 'membaik':            return '#3b82f6';
        case 'memburuk':           return '#f97316';
        case 'konsisten_belum':    return '#dc2626';
        default:                   return '#d1d5db';
    }
}
function transisiLabel(transisi) {
    switch (transisi) {
        case 'konsisten_memenuhi': return 'Konsisten Memenuhi';
        case 'membaik':            return 'Membaik';
        case 'memburuk':           return 'Memburuk';
        case 'konsisten_belum':    return 'Konsisten Belum Memenuhi';
        default:                   return '-';
    }
}
</script>
@endsection