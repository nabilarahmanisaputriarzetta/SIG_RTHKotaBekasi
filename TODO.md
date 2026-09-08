# TODO - Perubahan tabel kepadatan_penduduk

- [ ] Update Model `KepadatanPenduduk` agar sesuai kolom baru: `id, wadmkc, namobj, kepadatan_penduduk, tahun, gid`.
- [ ] Update Controller `AdminController` (metode `kepadatanIndex/store/update/destroy/export`) supaya:
  - filter `search` berdasarkan `namobj` (Kelurahan)
  - simpan/update menggunakan `updateOrCreate(['gid' => ..., 'tahun' => ...])`
- [ ] Update View `resources/views/admin/penduduk.blade.php` supaya UI form dan tabel memakai kolom baru:
  - Kelurahan: `namobj`
  - Kepadatan: `kepadatan_penduduk`
  - Tahun: `tahun`
  - wadmkc dan gid diisi/di-handle sesuai kebutuhan backend
- [ ] (Jika belum ada) tambahkan constraint UNIQUE pada tabel:
  - `data_rth_kelurahan_publik(gid, tahun)`
  - `kepadatan_penduduk(gid, tahun)`
- [ ] Tes manual:
  - `/admin/kepadatan` tambah/edit/hapus
  - export CSV

