/* ── Live table search ──────────────────────────
   Usage: <input oninput="filterTable(this.value,'my-table')">
   tableId defaults to 'data-table'
   Catatan: RTH & Penduduk punya fungsi filter sendiri
   (filterRthTable/filterPendudukTable) dengan filter tahun+status,
   jadi fungsi generik ini sengaja dibiarkan sebagai utilitas umum
   kalau suatu saat ada tabel admin lain yang cuma butuh search polos.
---------------------------------------------------*/
function filterTable(query, tableId = 'data-table') {
    const q = query.trim().toLowerCase();
    const rows = document.querySelectorAll(`#${tableId} tbody tr:not(.empty-row)`);
    let visible = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const show = !q || text.includes(q);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    // Show / hide empty state row
    const emptyRow = document.querySelector(`#${tableId} .empty-row`);
    if (emptyRow) emptyRow.style.display = visible === 0 ? '' : 'none';
}

/* ── Auto-dismiss flash alerts ──────────────── */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });
});