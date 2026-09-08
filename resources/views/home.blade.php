@extends('layouts.app')

@section('title', 'SIG RTH Bekasi')

@php
    $container = 'w-full max-w-[1200px] mx-auto px-6 max-[992px]:px-5 max-[480px]:px-4';
@endphp

@section('content')

<div id="rth-home" class="font-['DM_Sans',sans-serif] overflow-x-hidden">

{{--
     HERO --}}
<section class="rth-hero relative min-h-screen flex items-center pt-8 max-[1024px]:pt-12 max-[640px]:pt-8 overflow-hidden bg-[#f5f7f5]">

    {{-- Pattern background --}}
    <div class="hero-pattern-bg" aria-hidden="true"></div>

    {{-- Ambient orbs --}}
    <div class="orb orb-1" aria-hidden="true"></div>
    <div class="orb orb-2" aria-hidden="true"></div>
    <div class="orb orb-3" aria-hidden="true"></div>

    {{-- Dot grid texture --}}
    <div class="dot-grid" aria-hidden="true"></div>

    <div class="{{ $container }} relative z-10 py-4 max-[640px]:py-4">
        <div class="grid grid-cols-[1fr_420px] gap-16 items-center max-[1024px]:grid-cols-1 max-[1024px]:gap-12 max-[640px]:gap-8">

            {{-- LEFT --}}
            <div>

                <h1 class="hero-h1 reveal-up max-[640px]:!text-[2.1rem] max-[640px]:!leading-tight max-[400px]:!text-[1.8rem]" style="--d:80ms">
                    Pemetaan &amp;<br>
                    <em class="hero-em">Analisis RTH</em><br>
                    Kota Bekasi
                </h1>

                <p class="hero-sub reveal-up max-[640px]:!text-[0.95rem]" style="--d:160ms">
                    Visualisasi interaktif perubahan luas Ruang Terbuka Hijau
                    berdasarkan kelurahan untuk mendukung perencanaan kota
                    yang berkelanjutan.
                </p>

                <div class="flex gap-3 flex-wrap reveal-up" style="--d:240ms">
                    <a href="{{ route('peta') }}" class="btn-primary max-[400px]:w-full max-[400px]:justify-center">
                        Lihat Peta Interaktif
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('data') }}" class="btn-ghost max-[400px]:w-full max-[400px]:justify-center max-[400px]:text-center">
                        Lihat Data
                    </a>
                </div>
            </div>

            {{-- RIGHT - Year clock card --}}
            <div class="hero-card reveal-up max-[1024px]:max-w-[420px] max-[1024px]:mx-auto max-[1024px]:w-full max-[420px]:!p-5" style="--d:200ms">

                {{-- Year selector inside card --}}
                <div class="year-switcher flex-wrap gap-2">
                    @foreach($summaryByYear as $yr => $s)
                    <button class="yr-btn {{ $yr === $tahun ? 'yr-btn--active' : '' }}"
                            onclick="RTH.switchYear({{ $yr }})"
                            data-year="{{ $yr }}">{{ $yr }}</button>
                    @endforeach
                </div>

                {{-- Big number display --}}
                <div class="card-big-stat">
                    <div class="card-stat-label">Total Luas RTH</div>
                    <div class="card-stat-value max-[420px]:!text-[1.9rem]">
                        <span class="ctr" id="ctr-rth"
                              @foreach($summaryByYear as $yr => $s)
                              data-{{ $yr }}="{{ number_format($s['totalRth'],2,'.','') }}"
                              @endforeach
                              data-dec="2">{{ number_format($summary['totalRth'],2) }}</span>
                        <span class="card-unit">km²</span>
                    </div>
                </div>

                {{-- Mini stat row --}}
                <div class="card-mini-row max-[360px]:flex-wrap max-[360px]:gap-y-3">
                    <div class="mini-stat">
                        <span class="mini-label">RTH Publik Kota</span>
                        <span class="mini-val">
                            <span class="ctr" id="ctr-pct"
                                  @foreach($summaryByYear as $yr => $s)
                                  data-{{ $yr }}="{{ number_format($s['pctKota'],2,'.','') }}"
                                  @endforeach
                                  data-dec="2">{{ number_format($summary['pctKota'],2) }}</span>%
                        </span>
                    </div>
                    <div class="mini-divider"></div>
                    <div class="mini-stat">
                        <span class="mini-label">Memenuhi</span>
                        <span class="mini-val">
                            <span class="ctr" id="ctr-memenuhi"
                                  @foreach($summaryByYear as $yr => $s)
                                  data-{{ $yr }}="{{ $s['memenuhi'] }}"
                                  @endforeach
                                  data-dec="0">{{ $summary['memenuhi'] }}</span>
                            <span class="mini-sub">/ {{ $summary['jumlahKelurahan'] }}</span>
                        </span>
                    </div>
                    <div class="mini-divider"></div>
                    <div class="mini-stat">
                        <span class="mini-label">Belum</span>
                        <span class="mini-val warn">
                            <span class="ctr" id="ctr-dibawah"
                                  @foreach($summaryByYear as $yr => $s)
                                  data-{{ $yr }}="{{ $s['dibawah'] }}"
                                  @endforeach
                                  data-dec="0">{{ $summary['dibawah'] }}</span>
                        </span>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="card-progress-wrap">
                    <div class="card-progress-label">
                        <span>Kelurahan memenuhi target</span>
                        <span class="ctr-pct-text" id="pct-text">{{ round($summary['memenuhi']/$summary['jumlahKelurahan']*100) }}%</span>
                    </div>
                    <div class="card-progress-track">
                        <div class="card-progress-fill" id="prog-fill"
                             style="width: {{ round($summary['memenuhi']/$summary['jumlahKelurahan']*100) }}%"></div>
                    </div>
                </div>

            </div>

        </div>
    </div>

</section>


{{--
     STAT BENTO GRID --}}
<section class="py-14 sm:py-20 bg-[#f5f7f5]">
    <div class="{{ $container }}">

        <div class="section-label reveal-up">Data ringkasan</div>
        <h2 class="section-h2 reveal-up max-[480px]:!text-[1.5rem]" style="--d:60ms">
            Kondisi RTH Publik Kota Bekasi
            <span class="section-h2-year" style="font-size:0.5em;font-weight:600;color:var(--ink3,#9a9895);vertical-align:middle;margin-left:8px;">Tahun {{ $tahun }}</span>
        </h2>

        <div class="bento-grid reveal-up max-[640px]:!grid-cols-1" style="--d:120ms">

            {{-- Bento 1: big --}}
            <div class="bento-card bento-dark bento-span2 max-[640px]:!col-span-1">
                <div class="bento-eyebrow">Luas Total</div>
                <div class="bento-xl max-[420px]:!text-[2.2rem]">
                    <span class="ctr"
                          @foreach($summaryByYear as $yr => $s)
                          data-{{ $yr }}="{{ number_format($s['totalRth'],2,'.','') }}"
                          @endforeach
                          data-dec="2">{{ number_format($summary['totalRth'],2) }}</span>
                    <span class="bento-xl-unit">km²</span>
                </div>
                <div class="bento-sub">dari {{ number_format($summary['totalWilayah'],2) }} km² luas kota</div>
                <div class="bento-leaf max-[480px]:!hidden" aria-hidden="true">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                        <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                    </svg>
                </div>
            </div>

            {{-- Bento 2 --}}
            <div class="bento-card">
                <div class="bento-eyebrow">Persentase RTH</div>
                <div class="bento-lg max-[420px]:!text-[1.9rem]">
                    <span class="ctr"
                          @foreach($summaryByYear as $yr => $s)
                          data-{{ $yr }}="{{ number_format($s['pctKota'],2,'.','') }}"
                          @endforeach
                          data-dec="2">{{ number_format($summary['pctKota'],2) }}</span><span class="bento-lg-unit">%</span>
                </div>
                <div class="bento-sub">Rata-rata kota</div>
            </div>

            {{-- Bento 3: status --}}
            <div class="bento-card {{ $summary['statusKota'] === 'Memenuhi' ? 'bento-green' : 'bento-amber' }}">
                <div class="bento-eyebrow">Status</div>
                <div class="bento-lg bento-status-val max-[420px]:!text-[1.9rem]">{{ $summary['statusKota'] === 'Memenuhi' ? 'Tercapai' : 'Belum' }}</div>
                <div class="bento-sub">Target RTH ≥ 20%</div>
            </div>

            {{-- Bento 4: kelurahan --}}
            <div class="bento-card">
                <div class="bento-eyebrow">Kelurahan terdata</div>
                <div class="bento-lg max-[420px]:!text-[1.9rem]">{{ $summary['jumlahKelurahan'] }}</div>
                <div class="bento-sub">Seluruh wilayah</div>
            </div>

            {{-- Bento 5: donut visual --}}
            <div class="bento-card bento-donut-card">
                <div class="bento-eyebrow">Pemenuhan kelurahan</div>
                <div class="donut-wrap">
                    <svg class="donut-svg" viewBox="0 0 80 80">
                        <circle cx="40" cy="40" r="30" fill="none" stroke="#d0e8da" stroke-width="10"/>
                        <circle class="donut-arc" cx="40" cy="40" r="30" fill="none"
                                stroke="#256840" stroke-width="10"
                                stroke-dasharray="{{ round($summary['memenuhi']/$summary['jumlahKelurahan']*188.5,1) }} 188.5"
                                stroke-linecap="round"
                                transform="rotate(-90 40 40)"
                                id="donut-arc"/>
                    </svg>
                    <div class="donut-center">
                        <span class="donut-num ctr"
                              @foreach($summaryByYear as $yr => $s)
                              data-{{ $yr }}="{{ $s['memenuhi'] }}"
                              @endforeach
                              data-dec="0">{{ $summary['memenuhi'] }}</span>
                        <span class="donut-label">memenuhi</span>
                    </div>
                </div>
                <div class="donut-legend">
                    <span class="leg-dot leg-green"></span><span>Memenuhi</span>
                    <span class="leg-dot leg-gray" style="margin-left:12px"></span><span>Belum</span>
                </div>
            </div>

        </div>

    </div>
</section>


{{--
     ABOUT RTH - SPLIT--}}
<section class="py-14 sm:py-20 bg-white">
    <div class="{{ $container }}">

        <div class="split-grid">

            {{-- Image side --}}
            <div class="split-img-wrap reveal-left">
                <div class="split-img-frame">
                    <img src="{{ asset('images/bekasi-map.png') }}" alt="Peta Kota Bekasi" class="split-img">
                    {{-- Floating badge --}}
                    <div class="img-badge max-[640px]:!static max-[640px]:!mt-3 max-[640px]:!inline-flex max-[640px]:!items-center max-[640px]:!gap-2">
                        <span class="img-badge-num">{{ $summary['jumlahKelurahan'] }}</span>
                        <span class="img-badge-sub">kelurahan dipetakan</span>
                    </div>
                </div>
            </div>

            {{-- Text side --}}
            <div class="split-text reveal-right">
                <div class="section-label">Tentang RTH</div>
                <h2 class="section-h2 max-[480px]:!text-[1.5rem]" style="max-width:480px">Apa itu Ruang Terbuka Hijau?</h2>
                <p class="body-text">
                    Ruang Terbuka Hijau (RTH) adalah area memanjang/jalur dan/atau
                    mengelompok yang penggunaannya lebih bersifat terbuka, tempat
                    tumbuh tanaman baik secara alamiah maupun yang sengaja ditanam.
                </p>
                <p class="body-text">
                    Berdasarkan Permen PU No. 05/PRT/M/2008, RTH kelurahan disediakan
                    minimal <strong style="color:#256840">0,30 m²</strong> per penduduk
                    dengan luas minimal taman <strong style="color:#256840">9.000 m²</strong>
                    sesuai ketentuan penyediaan RTH skala kelurahan.
                </p>

                {{-- Benchmark pills --}}
                <div class="pill-row max-[480px]:flex-wrap">
                    <div class="info-pill">
                        <span class="pill-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#256840" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.4 2.4 0 0 1 0-3.4l2.6-2.6a2.4 2.4 0 0 1 3.4 0Z"/>
                                <path d="m14.5 12.5 2-2"/>
                                <path d="m11.5 9.5 2-2"/>
                                <path d="m8.5 6.5 2-2"/>
                                <path d="m17.5 15.5 2-2"/>
                            </svg>
                        </span>
                        <span>0,30 m²<br><small>per penduduk</small></span>
                    </div>
                    <div class="info-pill">
                        <span class="pill-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#256840" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 10v.2A3 3 0 0 1 8.9 16H5a3 3 0 0 1-1-5.8V10a3 3 0 0 1 6 0Z"/>
                                <path d="M7 16v6"/>
                                <path d="M13 19v3"/>
                                <path d="M12 19h8.3a1 1 0 0 0 .7-1.7L18 14h.3a1 1 0 0 0 .7-1.7L16 9h.2a1 1 0 0 0 .8-1.7L13 3l-1.4 1.5"/>
                            </svg>
                        </span>
                        <span>9.000 m²<br><small>min. per taman</small></span>
                    </div>
                    <div class="info-pill">
                        <span class="pill-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#256840" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
                                <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
                                <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
                                <path d="M10 6h4"/>
                                <path d="M10 10h4"/>
                                <path d="M10 14h4"/>
                                <path d="M10 18h4"/>
                            </svg>
                        </span>
                        <span>20%<br><small>target RTH publik kota</small></span>
                    </div>
                </div>

                <a href="{{ route('peta') }}" class="btn-primary mt-8 inline-flex max-[400px]:w-full max-[400px]:justify-center">
                    Lihat Peta Interaktif
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>

        </div>

    </div>
</section>

</div>{{-- #rth-home --}}



<script>
window.RTH = (function(){
    var activeYear = {{ $tahun }};
    var totalKel = {{ $summary['jumlahKelurahan'] }};

    // Data asli per tahun (dari HomeController::calcSummary), bukan dikarang lagi.
    var memData     = @json(collect($summaryByYear)->map(fn($s) => $s['memenuhi']));
    var jmlKelData  = @json(collect($summaryByYear)->map(fn($s) => $s['jumlahKelurahan']));
    var yearsList   = @json(array_values(array_map('intval', array_keys($summaryByYear))));

    function lerp(a, b, t){ return a + (b - a) * t; }
    function easeOut(t){ return 1 - Math.pow(1 - t, 3); }

    function animNum(el, from, to, dec, dur){
        dur = dur || 700;
        var start = null;
        var fromN = parseFloat(String(from).replace(',','.')) || 0;
        var toN   = parseFloat(String(to).replace(',','.'))   || 0;
        function frame(ts){
            if(!start) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            var v = lerp(fromN, toN, easeOut(p));
            el.textContent = dec > 0
                ? v.toFixed(dec).replace('.', ',')
                : Math.round(v).toLocaleString('id-ID');
            if(p < 1) requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
    }

    function switchYear(yr){
        if(yr === activeYear) return;
        activeYear = yr;

        // tabs
        document.querySelectorAll('.yr-btn').forEach(function(b){
            var isActive = parseInt(b.dataset.year) === yr;
            b.classList.toggle('yr-btn--active', isActive);
        });

        // counters
        document.querySelectorAll('.ctr').forEach(function(el){
            var to = el.dataset[yr];
            if(to === undefined) return; // tahun ini tidak punya data untuk elemen ini
            var dec = parseInt(el.dataset.dec) || 0;
            var from = el.textContent.replace(/\./g,'').replace(',','.');
            animNum(el, from, to, dec);
        });

        // progress bar & donut — pakai jumlah kelurahan tahun terkait
        // (jaga-jaga kalau jumlah kelurahan berubah antar tahun)
        var totalKelYr = jmlKelData[yr] || totalKel;
        var memenuhiYr = memData[yr] ?? 0;
        var pct = totalKelYr > 0 ? Math.round(memenuhiYr / totalKelYr * 100) : 0;
        var fill = document.getElementById('prog-fill');
        var pctText = document.getElementById('pct-text');
        if(fill) fill.style.width = pct + '%';
        if(pctText) pctText.textContent = pct + '%';

        // donut arc
        var arc = document.getElementById('donut-arc');
        if(arc){
            var dash = totalKelYr > 0 ? (memenuhiYr / totalKelYr * 188.5).toFixed(1) : '0';
            arc.setAttribute('stroke-dasharray', dash + ' 188.5');
        }
    }

    // Scroll reveal
    function initReveal(){
        var els = document.querySelectorAll('.reveal-up,.reveal-left,.reveal-right');
        var io = new IntersectionObserver(function(entries){
            entries.forEach(function(e){
                if(e.isIntersecting){ e.target.classList.add('revealed'); io.unobserve(e.target); }
            });
        }, { threshold: 0.12 });
        els.forEach(function(el){ io.observe(el); });
    }

    // Auto-play menyusuri semua tahun yang benar-benar ada di database,
    // lalu kembali ke tahun default (terbaru) supaya konsisten dengan
    // angka yang sudah dirender server-side.
    function autoPlay(){
        var others = yearsList.filter(function(y){ return y !== {{ $tahun }}; });
        others.forEach(function(y, i){
            setTimeout(function(){ switchYear(y); }, 1000 + i * 1200);
        });
        setTimeout(function(){ switchYear({{ $tahun }}); }, 1000 + others.length * 1200 + 1200);
    }

    document.addEventListener('DOMContentLoaded', function(){
        initReveal();
        autoPlay();
        // Trigger hero reveals immediately
        document.querySelectorAll('.rth-hero .reveal-up').forEach(function(el){ el.classList.add('revealed'); });
    });

    return { switchYear: switchYear };
})();
</script>

@endsection