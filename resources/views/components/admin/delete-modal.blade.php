@props([
    'id' => 'modal-delete'
])

<div
    class="modal-overlay modal-overlay--delete"
    id="{{ $id }}"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title">

    <div class="modal modal--confirm" style="max-width:420px">

        <div class="modal__accent"></div>

        <div class="modal__header">
            <span class="modal__title" id="{{ $id }}-title">
                Hapus data
            </span>

            <button
                type="button"
                class="modal__close"
                data-modal-close="{{ $id }}"
                aria-label="Tutup">

                <svg viewBox="0 0 24 24"
                     width="18"
                     height="18"
                     fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>

            </button>
        </div>


        <div class="modal__body">

            <div class="confirm-body">

                <h3>
                    Yakin ingin menghapus?
                </h3>


                <p>
                    Data <strong data-delete-label></strong>
                    akan dihapus secara permanen dan tidak dapat dipulihkan.
                </p>


            </div>

        </div>


        <div class="modal__footer">

            <button
                type="button"
                class="btn btn--outline"
                data-modal-close="{{ $id }}">

                Batal

            </button>


            <button
                type="button"
                class="btn btn--danger"
                data-delete-confirm>


                <span class="btn__spinner" aria-hidden="true"></span>


                <svg
                    class="btn__icon"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    width="16"
                    height="16"
                    aria-hidden="true"
                    style="margin-right:4px">

                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>

                </svg>


                <span class="btn__label">
                    Ya, Hapus
                </span>


            </button>

        </div>

    </div>

</div>


@once
<style>

/* =========================
   DELETE CONFIRM BODY
========================= */

.confirm-body {
    text-align:center;
    padding:.5rem 0 .75rem;
}


/* Icon tanpa animasi */

.confirm-icon {

    position:relative;
    width:60px;
    height:60px;

    display:flex;
    align-items:center;
    justify-content:center;

    margin:0 auto 16px;

    color:var(--text-danger);

}


/* Text */

.confirm-body h3 {

    font-size:15.5px;
    font-weight:600;

    color:var(--text-primary,#111);

    margin-bottom:8px;

}


.confirm-body p {

    font-size:13px;

    color:var(--text-muted,#888);

    line-height:1.6;

}


.confirm-body strong {

    color:var(--text-primary,#111);

    font-weight:700;

}



/* =========================
   BUTTON
========================= */


.btn {

    position:relative;

    overflow:hidden;

    display:inline-flex;

    align-items:center;

    justify-content:center;


    padding:0 16px;

    height:38px;

    border-radius:var(--radius,9px);

    font-size:13.5px;

    font-weight:600;


    cursor:pointer;


    border:1px solid var(--border-strong,#ccc);


    background:var(--surface-2,#fff);

    color:var(--text-primary,#111);


    text-decoration:none;


    transition:none;

}



.btn:hover {

    transform:none;

}


.btn:active {

    transform:none;

}



.btn--outline {

    background:transparent;

    border-color:var(--border-strong,#ccc);

}



.btn--outline:hover {

    background:var(--surface-1,#f5f5f5);

}




.btn--danger {

    background:linear-gradient(
        155deg,
        var(--danger,#dc2626),
        #b91c1c
    );


    color:#fff;

    border-color:transparent;


    box-shadow:
        0 2px 8px rgba(220,38,38,.28);

}



.btn--danger:hover {

    box-shadow:
        0 2px 8px rgba(220,38,38,.28);

}



/* Spinner tanpa putaran */

.btn__spinner {

    display:none;

    width:15px;

    height:15px;


    border:2px solid rgba(255,255,255,.4);

    border-top-color:#fff;

    border-radius:50%;

}



[data-delete-confirm].is-loading {

    pointer-events:none;

    opacity:.85;

}



[data-delete-confirm].is-loading .btn__spinner {

    display:inline-block;

}



[data-delete-confirm].is-loading .btn__icon {

    display:none;

}



[data-delete-confirm].is-loading .btn__label {

    font-size:0;

}



[data-delete-confirm].is-loading .btn__label::after {

    content:'Menghapus…';

    font-size:13.5px;

}



/* Matikan semua animasi */

.confirm-icon,
.confirm-body h3,
.confirm-body p,
.btn,
.btn *,
.btn__spinner {

    animation:none !important;

    transition:none !important;

}


</style>


<script>

if (!window.__deleteConfirmLoadingBound) {

    window.__deleteConfirmLoadingBound = true;


    document.addEventListener('click', e => {

        const btn = e.target.closest('[data-delete-confirm]');


        if (btn) {

            btn.classList.add('is-loading');

        }

    });

}

</script>

@endonce