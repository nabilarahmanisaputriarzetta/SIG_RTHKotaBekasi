@props([
    'id'       => 'modal',
    'title'    => 'Modal',
    'subtitle' => '',
    'icon'     => 'form',   {{-- 'form' | 'edit' | 'delete' --}}
    'size'     => 'md',
])

@php
$icons = [
    'form'   => '<path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>',
    'edit'   => '<path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>',
    'delete' => '<path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>',
];
$iconPath = $icons[$icon] ?? $icons['form'];

$sizes = [
    'sm' => '400px',
    'md' => '480px',
    'lg' => '640px',
    'xl' => '800px',
];
$maxWidth = $sizes[$size] ?? $sizes['md'];

$iconVariant = in_array($icon, ['form', 'edit', 'delete']) ? $icon : 'form';
@endphp

<div
    class="modal-overlay modal-overlay--{{ $iconVariant }}"
    id="{{ $id }}"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}Title"
    aria-hidden="true">

    <div class="modal" style="max-width: {{ $maxWidth }}">

        <div class="modal__accent"></div>

        {{-- Header --}}
        <div class="modal__header">
            <div class="modal__header-left">
                <span class="modal__icon modal__icon--{{ $iconVariant }}">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="19" height="19" aria-hidden="true">
                        {!! $iconPath !!}
                    </svg>
                </span>
                <div>
                    <div class="modal__title" id="{{ $id }}Title">{{ $title }}</div>
                    @if($subtitle)
                        <div class="modal__subtitle">{{ $subtitle }}</div>
                    @endif
                </div>
            </div>
            <button
                type="button"
                class="modal__close"
                data-modal-close="{{ $id }}"
                aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="modal__body">
            <input type="hidden" name="id">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @isset($footer)
            <div class="modal__footer">
                {{ $footer }}
            </div>
        @endisset

    </div>
</div>

@once
<style>
:root {
    --modal-primary:      var(--primary, #1a6b4a);
    --modal-primary-dk:   var(--primary-dk, #145538);
    --modal-primary-lt:   var(--primary-lt, #e6f4ed);
    --modal-blue:         var(--blue, #2563eb);
    --modal-blue-lt:      var(--blue-lt, #eff6ff);
    --modal-danger:       var(--danger, #dc2626);
    --modal-danger-lt:    var(--danger-lt, #fef2f2);
    --modal-ease:         cubic-bezier(.22, 1, .36, 1);
    --modal-ease-spring:  cubic-bezier(.34, 1.56, .64, 1);
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(10, 20, 15, 0.55);
    backdrop-filter: blur(0px);
    -webkit-backdrop-filter: blur(0px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 200;
    padding: 1rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity .28s ease, backdrop-filter .28s ease;
}

.modal-overlay.open {
    opacity: 1;
    pointer-events: all;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.modal {
    position: relative;
    background: var(--surface-2, #fff);
    border-radius: 16px;
    border: 1px solid var(--border, #e5e5e5);
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    overflow-x: hidden;
    transform: translateY(24px) scale(.94);
    opacity: 0;
    transition: transform .32s var(--modal-ease-spring), opacity .22s ease;
    box-shadow: 0 24px 70px rgba(10, 30, 20, .22), 0 4px 14px rgba(10, 30, 20, .10);
}

.modal-overlay.open .modal {
    transform: translateY(0) scale(1);
    opacity: 1;
}

.modal__accent {
    position: sticky;
    top: 0;
    height: 3px;
    width: 100%;
    background: linear-gradient(90deg, var(--modal-primary), #4db87a, var(--modal-primary));
    background-size: 200% auto;
    animation: modalAccentFlow 3.5s linear infinite;
}
.modal-overlay--edit .modal__accent { background: linear-gradient(90deg, var(--modal-blue), #60a5fa, var(--modal-blue)); background-size: 200% auto; }
.modal-overlay--delete .modal__accent { background: linear-gradient(90deg, var(--modal-danger), #f87171, var(--modal-danger)); background-size: 200% auto; }

@keyframes modalAccentFlow {
    to { background-position: -200% center; }
}

.modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.4rem;
    border-bottom: 1px solid var(--border, #e5e5e5);
    position: sticky;
    top: 3px;
    background: var(--surface-2, #fff);
    z-index: 1;
}

.modal__header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.modal__icon {
    flex-shrink: 0;
    width: 38px;
    height: 38px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: modalIconPop .4s var(--modal-ease-spring) .05s both;
    box-shadow: 0 2px 6px rgba(0,0,0,.06);
}
.modal__icon--form   { background: linear-gradient(150deg, var(--modal-primary-lt), #dcf3e6); color: var(--modal-primary); }
.modal__icon--edit   { background: linear-gradient(150deg, var(--modal-blue-lt), #dbeafe); color: var(--modal-blue); }
.modal__icon--delete { background: linear-gradient(150deg, var(--modal-danger-lt), #fee2e2); color: var(--modal-danger); }

@keyframes modalIconPop {
    from { transform: scale(.4) rotate(-12deg); opacity: 0; }
    to   { transform: scale(1) rotate(0deg); opacity: 1; }
}

.modal__title {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-primary, #111);
    letter-spacing: -.1px;
}

.modal__subtitle {
    font-size: 12px;
    color: var(--text-muted, #888);
    margin-top: 2px;
}

.modal__close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    border: 1px solid var(--border, #e5e5e5);
    background: transparent;
    cursor: pointer;
    color: var(--text-secondary, #555);
    flex-shrink: 0;
}

.modal__close:active { transform: rotate(90deg) scale(.88); }

.modal__body {
    padding: 1.4rem;
    animation: modalBodyFade .35s ease .08s both;
}

@keyframes modalBodyFade {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.modal__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    padding: 1rem 1.4rem;
    border-top: 1px solid var(--border, #e5e5e5);
    background: var(--surface-1, #f9f9f9);
    position: sticky;
    bottom: 0;
}

/* Buttons — shared premium treatment */
.btn {
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    height: 38px;
    border-radius: 9px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--border-strong, #ccc);
    background: var(--surface-2, #fff);
    color: var(--text-primary, #111);
    transition: background .18s ease, border-color .18s ease, transform .15s ease, box-shadow .18s ease;
    text-decoration: none;
}

.btn:hover { transform: translateY(-1px); }
.btn:active { transform: translateY(0) scale(.97); }

.btn--outline {
    background: transparent;
    border-color: var(--border-strong, #ccc);
}
.btn--outline:hover { background: var(--surface-1, #f5f5f5); }

.btn--danger {
    background: linear-gradient(155deg, var(--modal-danger), #b91c1c);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(220,38,38,.28);
}
.btn--danger:hover {
    box-shadow: 0 6px 16px rgba(220,38,38,.35);
}

.btn--primary {
    background: linear-gradient(155deg, var(--modal-primary), var(--modal-primary-dk));
    color: #fff;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(26,107,74,.28);
}
.btn--primary:hover {
    box-shadow: 0 6px 16px rgba(26,107,74,.35);
}

/* subtle ripple */
.btn .btn__ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,.55);
    transform: scale(0);
    animation: btnRipple .5s ease-out forwards;
    pointer-events: none;
}
@keyframes btnRipple {
    to { transform: scale(2.6); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .modal, .modal__icon, .modal__body, .modal-overlay, .modal__accent, .btn {
        animation: none !important;
        transition: none !important;
    }
}

/* Custom dropdown (replaces native <select> popup so it doesn't
   render flush against the field — see .xselect__menu below) */
.xselect {
    position: relative;
    width: 100%;
}

.xselect__native {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    pointer-events: none;
}

.xselect__trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    width: 100%;
    padding: 10px 13px;
    border: 1.5px solid var(--border, #dde5df);
    border-radius: var(--r-sm, 8px);
    background: var(--surface, #fff);
    color: var(--text, #111827);
    font: inherit;
    font-size: 13.5px;
    text-align: left;
    cursor: pointer;
    outline: none;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
    transition: border-color .12s ease, box-shadow .12s ease;
}

.xselect__trigger:hover { border-color: var(--border-md, #c8d6cb); }
.xselect__trigger:disabled { background: var(--bg, #f0f4f1); color: var(--muted, #6b7280); cursor: default; }
.xselect__trigger.is-placeholder { color: var(--muted-lt, #9ca3af); }

.xselect.open .xselect__trigger {
    border-color: var(--modal-primary);
    box-shadow: 0 0 0 3px rgba(26,107,74,.12);
}

.xselect__label {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.xselect__chevron {
    flex-shrink: 0;
    color: var(--text-muted, #888);
    transition: transform .18s ease;
}

.xselect.open .xselect__chevron { transform: rotate(180deg); }

/* The gap here (top: calc(100% + 6px)) is what keeps the panel from
   sitting flush against the field, plus its own rounded corners + shadow */
.xselect__menu {
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 6px);
    background: var(--surface-2, #fff);
    border: 1px solid var(--border, #e5e5e5);
    border-radius: 12px;
    box-shadow: 0 14px 32px rgba(10,30,20,.16), 0 4px 10px rgba(10,30,20,.08);
    padding: 6px;
    max-height: 240px;
    overflow-y: auto;
    z-index: 300;
    opacity: 0;
    transform: translateY(-6px) scale(.98);
    pointer-events: none;
    transition: opacity .16s ease, transform .16s ease;
}

.xselect.open .xselect__menu {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: all;
}

.xselect__option {
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 13.5px;
    color: var(--text-primary, #111827);
    cursor: pointer;
}

.xselect__option:hover,
.xselect__option.is-active { background: var(--surface-1, #f5f5f5); }

.xselect__option.is-selected {
    background: var(--modal-primary-lt);
    color: var(--modal-primary-dk);
    font-weight: 600;
}

@media (prefers-reduced-motion: reduce) {
    .xselect__menu, .xselect__chevron { transition: none !important; }
}
</style>

<script>
/* ── Custom dropdown for <select class="form-input"> ──────────
   Native <select> keeps the real value (so all existing code that
   reads/sets `select.value` or listens for `change`/`input` keeps
   working untouched) — it's just visually hidden. A styled button
   + floating panel sit on top of it for the actual UI. */
function enhanceSelects(root = document) {
    root.querySelectorAll('select.form-input:not([data-xselect-ready])').forEach(sel => {
        sel.dataset.xselectReady = '1';

        const wrap = document.createElement('div');
        wrap.className = 'xselect';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);
        sel.classList.add('xselect__native');
        sel.tabIndex = -1;

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'xselect__trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.innerHTML = `
            <span class="xselect__label"></span>
            <svg class="xselect__chevron" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                <path d="M7 10l5 5 5-5z"/>
            </svg>`;
        wrap.appendChild(trigger);

        const menu = document.createElement('div');
        menu.className = 'xselect__menu';
        menu.setAttribute('role', 'listbox');
        wrap.appendChild(menu);

        const label = trigger.querySelector('.xselect__label');

        function buildMenu() {
            menu.innerHTML = '';
            Array.from(sel.options).forEach(opt => {
                if (opt.disabled) return;
                const item = document.createElement('div');
                item.className = 'xselect__option' + (opt.value === sel.value ? ' is-selected' : '');
                item.setAttribute('role', 'option');
                item.textContent = opt.textContent.trim();
                item.dataset.value = opt.value;
                item.addEventListener('click', () => {
                    sel.value = opt.value;
                    sel.dispatchEvent(new Event('input', { bubbles: true }));
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                    closeMenu();
                });
                menu.appendChild(item);
            });
        }

        function syncTrigger() {
            const opt = sel.options[sel.selectedIndex];
            const hasValue = opt && opt.value !== '';
            label.textContent = opt ? opt.textContent.trim() : '';
            trigger.classList.toggle('is-placeholder', !hasValue);
            trigger.disabled = sel.disabled;
            menu.querySelectorAll('.xselect__option').forEach(o => {
                o.classList.toggle('is-selected', o.dataset.value === sel.value);
            });
        }

        function openMenu() {
            document.querySelectorAll('.xselect.open').forEach(w => w !== wrap && w.classList.remove('open'));
            buildMenu();
            wrap.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            wrap.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', e => {
            e.stopPropagation();
            wrap.classList.contains('open') ? closeMenu() : openMenu();
        });

        document.addEventListener('click', e => {
            if (!wrap.contains(e.target)) closeMenu();
        });

        // Stay in sync whenever the underlying select's value changes,
        // whether from our own click above or from other scripts.
        sel.addEventListener('change', syncTrigger);

        wrap._xselectClose = closeMenu;
        syncTrigger();
    });
}

document.addEventListener('DOMContentLoaded', () => {

    enhanceSelects();

    // Ripple on any .btn click
    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn');
        if (!btn) return;
        const rect = btn.getBoundingClientRect();
        const ripple = document.createElement('span');
        const size = Math.max(rect.width, rect.height);
        ripple.className = 'btn__ripple';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 500);
    });

    // Open
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modalOpen);
            if (modal) {
                enhanceSelects(modal);
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            }
        });
    });

    // Close (button)
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modalClose);
            if (modal) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    });

    // Close (backdrop)
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) {
                overlay.classList.remove('open');
                overlay.setAttribute('aria-hidden', 'true');
            }
        });
    });

    // Close on Escape (an open dropdown panel closes first, then the modal)
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            const openSelect = document.querySelector('.xselect.open');
            if (openSelect) {
                openSelect._xselectClose?.();
                return;
            }
            document.querySelectorAll('.modal-overlay.open').forEach(m => {
                m.classList.remove('open');
                m.setAttribute('aria-hidden', 'true');
            });
        }
    });
});
</script>
@endonce