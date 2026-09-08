@extends('layouts.app')

@section('title', 'Login Admin')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>html,body{overflow:hidden}</style>
@endpush

@section('content')

<div class="login-page" id="loginPage">

    {{-- ── BACKGROUND ──────────────────────── --}}
    <div class="bg-layer">
        <div class="bg-img" id="bgImg"
             style="background-image:url('{{ asset('images/background-login.png') }}')"></div>
        <div class="bg-overlay"></div>

        {{-- Floating leaf orbs --}}
        <div class="orb orb-a" aria-hidden="true"></div>
        <div class="orb orb-b" aria-hidden="true"></div>
        <div class="orb orb-c" aria-hidden="true"></div>

        {{-- Grid dots --}}
        <div class="dot-grid" aria-hidden="true"></div>

        {{-- Animated particles --}}
        <div class="particles" id="particles" aria-hidden="true"></div>
    </div>

    {{-- ── LEFT PANEL (info) ───────────────── --}}
    <div class="info-panel" id="infoPanel">
        <div class="info-inner">

            <h1 class="info-title">
                Ruang<br>
                <em>Terbuka</em><br>
                Hijau
            </h1>

            <p class="info-sub">
                Sistem Informasi Geografis Pemetaan
                dan Analisis RTH Publik Kota Bekasi.
            </p>

            <div class="info-stats">
                <div class="istat">
                    <span class="istat-num" id="s1">0</span>
                    <span class="istat-label">Kelurahan</span>
                </div>
                <div class="istat-div"></div>
                <div class="istat">
                    <span class="istat-num">20<span style="font-size:18px">%</span></span>
                    <span class="istat-label">Target RTH Publik</span>
                </div>
                <div class="istat-div"></div>
                <div class="istat">
                    <span class="istat-num">9<span style="font-size:18px">m²</span></span>
                    <span class="istat-label">WHO / jiwa</span>
                </div>
            </div>

            <div class="info-line"></div>
            <p class="info-copy">UU No. 26 Tahun 2007 · Pasal 29</p>

        </div>
    </div>

    {{-- ── CARD ────────────────────────────── --}}
    <div class="card-wrap">
        <div class="login-card" id="loginCard">

            {{-- Shimmer ring --}}
            <div class="card-ring" aria-hidden="true"></div>

            {{-- Header --}}
            <div class="card-header">
                <div class="">
                </div>
                <p class="card-welcome">Welcome to</p>
                <h2 class="card-brand font-['DM_Serif_Display',serif]">
                    RTH Publik Kota <span class="brand-accent">Bekasi</span>
                </h2>
                <p class="card-hint">Panel Admin</p>
            </div>

            {{-- Error --}}
            @if($errors->any())
            <div class="err-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                {{ $errors->first() }}
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login.post') }}" autocomplete="off" id="loginForm">
                @csrf

                {{-- Email --}}
                <div class="field" id="field-email">
                    <label class="field-label">Email</label>
                    <div class="field-wrap">
                        <svg class="field-ico" width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                        <input class="field-input font-['Outfit',sans-serif]"
                               type="email" name="email"
                               placeholder="demo@email.com"
                               value="{{ old('email') }}"
                               required
                               onfocus="activateField('field-email')"
                               onblur="deactivateField('field-email', this)">
                        <div class="field-check" id="chk-email" aria-hidden="true">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        </div>
                    </div>
                    <div class="field-bar"><div class="field-bar-fill"></div></div>
                </div>

                {{-- Password --}}
                <div class="field" id="field-password">
                    <label class="field-label">Password</label>
                    <div class="field-wrap">
                        <svg class="field-ico" width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                        </svg>
                        <input class="field-input font-['Outfit',sans-serif]"
                               type="password" id="password" name="password"
                               placeholder="enter your password"
                               required
                               onfocus="activateField('field-password')"
                               onblur="deactivateField('field-password', this)">
                        <button type="button" class="eye-btn" id="eyeBtn"
                                onclick="togglePassword()" aria-label="Tampilkan password">
                            <svg id="eye-open" width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                            <svg id="eye-shut" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" style="display:none">
                                <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 001 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="field-bar"><div class="field-bar-fill"></div></div>
                </div>

                {{-- Strength meter (shows when typing password) --}}
                <div class="strength-wrap" id="strengthWrap">
                    <div class="strength-bars">
                        <div class="sbar" id="sb1"></div>
                        <div class="sbar" id="sb2"></div>
                        <div class="sbar" id="sb3"></div>
                        <div class="sbar" id="sb4"></div>
                    </div>
                    <span class="strength-label" id="strengthLabel"></span>
                </div>

                {{-- Submit --}}
                <button type="submit" class="submit-btn font-['Outfit',sans-serif]" id="submitBtn">
                    <span class="btn-text">Masuk ke Dashboard</span>
                    <span class="btn-arrow">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                    <span class="btn-loader" id="btnLoader" aria-hidden="true"></span>
                </button>

            </form>

        </div>
    </div>

</div>

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

.login-page {
    position: fixed; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Outfit', sans-serif;
    overflow: hidden;
}

/* ── Background ──────────────────────── */
.bg-layer { position: absolute; inset: 0; }
.bg-img {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    filter: grayscale(0.5);
    transition: transform 20s ease;
}
.bg-overlay {
    position: absolute; inset: 0;
    background: rgba(255,255,255,.48);
}
.orb {
    position: absolute; border-radius: 50%; pointer-events: none;
}
.orb-a {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(45,122,78,.14) 0%, transparent 70%);
    top: -120px; left: -80px;
    animation: drift1 14s ease-in-out infinite;
}
.orb-b {
    width: 380px; height: 380px;
    background: radial-gradient(circle, rgba(63,143,96,.1) 0%, transparent 70%);
    bottom: -60px; right: -60px;
    animation: drift2 18s ease-in-out infinite;
}
.orb-c {
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(184,217,197,.3) 0%, transparent 70%);
    top: 40%; left: 30%;
    animation: drift1 22s ease-in-out infinite reverse;
}
@keyframes drift1{0%,100%{transform:translate(0,0)}50%{transform:translate(30px,-25px)}}
@keyframes drift2{0%,100%{transform:translate(0,0)}50%{transform:translate(-20px,30px)}}

.dot-grid {
    position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(45,122,78,.2) 1px, transparent 1px);
    background-size: 28px 28px;
}
.particles { position: absolute; inset: 0; overflow: hidden; }
.particle {
    position: absolute;
    width: 4px; height: 4px;
    border-radius: 50%;
    background: rgba(45,122,78,.35);
    animation: floatUp linear infinite;
}
@keyframes floatUp {
    0%   { transform: translateY(0) scale(1);   opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: .5; }
    100% { transform: translateY(-100vh) scale(.4); opacity: 0; }
}

/* ── Info panel (left) ───────────────── */
.info-panel {
    position: relative; z-index: 2;
    width: 340px; padding: 56px 48px;
    display: flex; align-items: center;
    margin-right: 40px;
    opacity: 0; transform: translateX(-30px);
    animation: slideIn .9s cubic-bezier(.34,1.2,.64,1) .2s forwards;
}
@media(max-width:900px){ .info-panel{display:none} }

.info-badge {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 11px; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: #2d7a4e;
    background: rgba(255,255,255,.9); border: 1px solid #b8d9c5;
    border-radius: 99px; padding: 6px 14px; margin-bottom: 32px;
}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

.info-title {
    font-family: 'DM Serif Display', serif;
    font-size: 52px; line-height: 1.05;
    color: #1b2e1f; margin-bottom: 20px;
    letter-spacing: -.02em;
}
.info-title em { font-style: italic; color: #2d7a4e; }

.info-sub {
    font-size: 14px; line-height: 1.8; color: #4a6650;
    margin-bottom: 36px; max-width: 260px;
}

.info-stats {
    display: flex; align-items: center; gap: 0;
    background: rgba(255,255,255,.75); border: 1px solid #d0e8da;
    border-radius: 18px; overflow: hidden; margin-bottom: 28px;
}
.istat { flex: 1; padding: 16px 14px; text-align: center; }
.istat-num {
    display: block; font-size: 28px; font-weight: 800;
    color: #1b2e1f; line-height: 1;
}
.istat-label {
    display: block; font-size: 10px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .06em;
    color: #7a9484; margin-top: 4px;
}
.istat-div { width: 1px; background: #d0e8da; align-self: stretch; }

.info-line { height: 1px; background: rgba(184,217,197,.5); margin-bottom: 16px; }
.info-copy { font-size: 11px; color: #8aab97; font-weight: 600; letter-spacing: .04em; }

/* ── Card ────────────────────────────── */
.card-wrap {
    position: relative; z-index: 2;
    opacity: 0; transform: translateY(28px) scale(.97);
    animation: slideIn .85s cubic-bezier(.34,1.2,.64,1) .1s forwards;
}
@keyframes slideIn{to{opacity:1;transform:none}}

.login-card {
    position: relative;
    width: 420px;
    background: rgba(255,255,255,.96);
    border-radius: 24px;
    padding: 40px 40px 36px;
    box-shadow: 0 24px 80px rgba(0,0,0,.14), 0 0 0 1px rgba(184,217,197,.4);
    backdrop-filter: blur(12px);
}
@media(max-width:480px){
    .login-card{width:calc(100vw - 32px);padding:32px 24px 28px}
}

.card-ring {
    position: absolute; inset: -2px; border-radius: 26px;
    border: 2px solid transparent;
    background: linear-gradient(135deg, rgba(63,143,96,.3), transparent 60%, rgba(45,122,78,.15)) border-box;
    -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: destination-out;
    mask-composite: exclude;
    pointer-events: none;
    animation: ring-glow 4s ease-in-out infinite;
}
@keyframes ring-glow{0%,100%{opacity:.6}50%{opacity:1}}

/* ── Card header ─────────────────────── */
.card-header { text-align: center; margin-bottom: 32px; }
.card-logo {
    width: 52px; height: 52px;
    background: #edf7f1; border: 1.5px solid #b8d9c5;
    border-radius: 16px; display: flex; align-items: center;
    justify-content: center; margin: 0 auto 18px;
    animation: logo-bob 3s ease-in-out infinite;
}
@keyframes logo-bob{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}
.card-welcome { font-size: 13px; font-weight: 500; color: #888; margin-bottom: 4px; }
.card-brand {
    font-size: 26px; line-height: 1.2; color: #1b2e1f;
    letter-spacing: -.01em; margin-bottom: 6px;
}
.brand-accent { color: #3f8f60; }
.card-hint { font-size: 11px; font-weight: 700; text-transform: uppercase;
             letter-spacing: .1em; color: #b0c4b8; }

/* ── Error ───────────────────────────── */
.err-box {
    display: flex; align-items: center; gap: 8px;
    background: #fef2f2; border: 1px solid #fecaca;
    color: #dc2626; border-radius: 10px;
    padding: 10px 14px; font-size: 12px; margin-bottom: 20px;
    animation: shake .4s cubic-bezier(.36,.07,.19,.97);
}
@keyframes shake{10%,90%{transform:translateX(-2px)}20%,80%{transform:translateX(3px)}30%,50%,70%{transform:translateX(-3px)}40%,60%{transform:translateX(3px)}}

/* ── Fields ──────────────────────────── */
.field { margin-bottom: 22px; }
.field-label {
    display: block; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .08em;
    color: #8aab97; margin-bottom: 10px;
    transition: color .2s;
}
.field.is-active .field-label { color: #2d7a4e; }

.field-wrap {
    display: flex; align-items: center; gap: 10px;
    background: #f8faf8; border-radius: 12px;
    padding: 12px 14px;
    border: 1.5px solid #e8f0ea;
    transition: border-color .25s, background .25s, box-shadow .25s;
}
.field.is-active .field-wrap {
    border-color: #2d7a4e;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(45,122,78,.1);
}
.field.is-filled .field-wrap { border-color: #3f8f60; }

.field-ico { color: #c0cfc4; flex-shrink: 0; transition: color .2s; }
.field.is-active .field-ico { color: #2d7a4e; }

.field-input {
    flex: 1; border: none; outline: none; background: transparent;
    font-size: 14px; color: #1b2e1f;
}
.field-input::placeholder { color: #c0cfc4; }

.field-check {
    width: 18px; height: 18px; border-radius: 50%;
    background: #3f8f60; color: white;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transform: scale(.4);
    transition: opacity .25s, transform .35s cubic-bezier(.34,1.56,.64,1);
    flex-shrink: 0;
}
.field.is-filled .field-check { opacity: 1; transform: scale(1); }

.field-bar { height: 2px; background: #e8f0ea; border-radius: 99px; margin-top: 6px; overflow: hidden; }
.field-bar-fill {
    height: 100%; width: 0%; border-radius: 99px;
    background: #2d7a4e;
    transition: width .35s ease;
}
.field.is-active .field-bar-fill { width: 100%; }

/* ── Eye button ──────────────────────── */
.eye-btn {
    border: none; background: none; cursor: pointer;
    color: #c0cfc4; padding: 2px; display: flex;
    transition: color .2s, transform .15s;
}
.eye-btn:hover { color: #2d7a4e; transform: scale(1.15); }

/* ── Password strength ───────────────── */
.strength-wrap {
    display: flex; align-items: center; gap: 10px;
    margin-top: -10px; margin-bottom: 22px;
    height: 0; overflow: hidden; opacity: 0;
    transition: height .3s, opacity .3s;
}
.strength-wrap.visible { height: 22px; opacity: 1; }
.strength-bars { display: flex; gap: 4px; flex: 1; }
.sbar {
    flex: 1; height: 4px; border-radius: 99px;
    background: #e8f0ea;
    transition: background .3s;
}
.sbar.s-weak   { background: #ef4444; }
.sbar.s-fair   { background: #f59e0b; }
.sbar.s-good   { background: #3f8f60; }
.sbar.s-strong { background: #2d7a4e; }
.strength-label { font-size: 11px; font-weight: 700; color: #8aab97; white-space: nowrap; }

/* ── Submit button ───────────────────── */
.submit-btn {
    width: 100%; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 10px;
    padding: 15px 24px;
    background: #2d7a4e; color: white;
    border-radius: 14px;
    font-size: 15px; font-weight: 600;
    margin-top: 6px;
    transition: transform .2s, box-shadow .2s, background .2s;
    position: relative; overflow: hidden;
}
.submit-btn::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.15), transparent);
    transform: translateX(-100%);
    transition: transform .6s;
}
.submit-btn:hover {
    background: #236040;
    transform: translateY(-2px);
    box-shadow: 0 14px 32px rgba(45,122,78,.32);
}
.submit-btn:hover::before { transform: translateX(100%); }
.submit-btn:active { transform: translateY(0) scale(.98); }
.btn-arrow { display: flex; transition: transform .25s; }
.submit-btn:hover .btn-arrow { transform: translateX(3px); }
.btn-loader {
    width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,.35);
    border-top-color: white;
    border-radius: 50%;
    display: none;
    animation: spin .7s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}
.submit-btn.loading .btn-text,
.submit-btn.loading .btn-arrow { opacity: 0; }
.submit-btn.loading .btn-loader { display: block; position: absolute; }
</style>

<script>
(function(){

    /* ── Parallax bg on mouse move ───────────── */
    var bgImg = document.getElementById('bgImg');
    document.addEventListener('mousemove', function(e){
        var xPct = (e.clientX / window.innerWidth  - .5) * 12;
        var yPct = (e.clientY / window.innerHeight - .5) * 8;
        bgImg.style.transform = 'translate(' + xPct + 'px,' + yPct + 'px) scale(1.06)';
    }, {passive:true});

    /* ── Card tilt on mouse move ─────────────── */
    var card = document.getElementById('loginCard');
    card.addEventListener('mousemove', function(e){
        var r  = card.getBoundingClientRect();
        var x  = (e.clientX - r.left) / r.width  - .5;
        var y  = (e.clientY - r.top)  / r.height - .5;
        card.style.transform = 'perspective(900px) rotateY(' + (x * 5) + 'deg) rotateX(' + (-y * 4) + 'deg) translateZ(4px)';
    });
    card.addEventListener('mouseleave', function(){
        card.style.transform = 'perspective(900px) rotateY(0) rotateX(0) translateZ(0)';
        card.style.transition = 'transform .5s ease';
    });
    card.addEventListener('mouseenter', function(){
        card.style.transition = 'transform .1s ease';
    });

    /* ── Animated counter for kelurahan stat ─── */
    function countUp(el, target, dur){
        var start = null;
        function frame(ts){
            if(!start) start = ts;
            var p = Math.min((ts-start)/dur, 1);
            var e = 1 - Math.pow(1-p, 3);
            el.textContent = Math.round(e * target);
            if(p < 1) requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
    }
    setTimeout(function(){ countUp(document.getElementById('s1'), 56, 1800); }, 600);

    /* ── Field interactions ──────────────────── */
    window.activateField = function(id){
        document.getElementById(id).classList.add('is-active');
    };
    window.deactivateField = function(id, input){
        document.getElementById(id).classList.remove('is-active');
        if(input.value.trim()) document.getElementById(id).classList.add('is-filled');
        else document.getElementById(id).classList.remove('is-filled');
    };

    /* ── Toggle password ─────────────────────── */
    window.togglePassword = function(){
        var inp = document.getElementById('password');
        var isText = inp.type === 'text';
        inp.type = isText ? 'password' : 'text';
        document.getElementById('eye-open').style.display = isText ? '' : 'none';
        document.getElementById('eye-shut').style.display = isText ? 'none' : '';
    };

    /* ── Submit loader ───────────────────────── */
    document.getElementById('loginForm').addEventListener('submit', function(){
        var btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
    });

})();
</script>

@endsection