# CLAUDE.md
 
Konteks proyek untuk AI. Aplikasi web absensi mahasiswa per kelas untuk dosen (fullstack: frontend, backend, database).
 
## Peran
- **Saya**: pengambil keputusan dan pelaksana (menjalankan perintah, menempel kode, mengetes).
- **AI**: perencana dan pemecah masalah. Beri opsi + satu rekomendasi untuk keputusan penting, jangan memutuskan sendiri. Berikan kode siap tempel beserta lokasi file dan cara tesnya. Saat error, diagnosis dulu dan minta bukti (pesan error, `route:list`, `storage/logs/laravel.log`) sebelum mengubah apa pun.
- Bahasa Indonesia, ringkas, akhiri dengan satu langkah lanjutan.
## Stack
Laravel, MySQL (phpMyAdmin), auth Laravel Breeze Blade (session), Tailwind CSS, JavaScript biasa. Halaman Blade hanya kerangka; data diambil lewat `fetch` ke `/api/...` tanpa reload.
 
## Database
`users`, `kelas`, `mahasiswas`, `kelas_mahasiswa` (pendaftaran), `pertemuans`, `kehadirans`. `kehadirans` menunjuk ke `kelas_mahasiswa` agar hanya mahasiswa terdaftar yang bisa dicatat. FK memakai `ON DELETE CASCADE`. **Tabel dibuat manual lewat SQL** (`database/schema.sql`, `database/dummy.sql`).
 
## Aturan bisnis
1. Hanya mahasiswa terdaftar di kelas itu yang bisa dicatat kehadirannya.
2. Status hanya `hadir`, `izin`, `sakit`, `alpa`.
3. Izin dan sakit **dihitung tidak hadir**, tetap tercatat dan wajib ada keterangan.
4. Persentase = `hadir ÷ total pertemuan × 100` (pertemuan belum diinput tetap masuk penyebut).
5. `belum_diisi` hanya tampilan, tidak pernah disimpan atau dikirim ke API.
6. Input kehadiran bulk (satu layar semua mahasiswa), `updateOrCreate` dalam transaksi.
## Struktur
`app/Http/Controllers/Api/` (6 controller), `PageController` (hanya return view), `routes/api.php` (nama `api.`), `routes/web.php` (nama `page.`, middleware `auth`), `public/js/api.js` (helper fetch), `resources/views/{kelas,mahasiswa,pertemuan,kehadiran,rekap}/`.
 
## Jebakan yang sudah diketahui
- Model `Kelas` wajib `$table = 'kelas'`; route `kelas` wajib `->parameters(['kelas' => 'kelas'])` (kalau tidak jadi `{kela}`).
- Method controller harus sama dengan route (error "undefined method index()" = route menunjuk method yang tidak ada).
- Tes API: header `Accept: application/json`, body PUT berupa raw JSON. Dari halaman web, `fetch` butuh `X-CSRF-TOKEN`.
- Tailwind butuh `npm run dev` (atau `npm run build`). Setelah ubah route: `php artisan route:clear`.
## Jangan tanpa persetujuan saya
`php artisan migrate`/migration, ubah skema atau aturan bisnis, ubah file auth Breeze, tambah framework/paket baru, hapus data atau file.
 