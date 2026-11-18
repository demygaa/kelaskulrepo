# AI_NOTES.md
 
Catatan singkat tentang cara saya bekerja bersama AI (Claude) di proyek ini.
 
## Pembagian kerja
Saya yang memutuskan dan menjalankan, AI yang merencanakan dan memecahkan masalah. AI memberi opsi dan rekomendasi, saya yang memilih. Kode dari AI saya tempel dan jalankan sendiri, bukan diterapkan otomatis.
 
## Alur kerja
1. Saya tempel ketentuan tugas, lalu minta rancangan database.
2. Saya memberi arahan, AI menyesuaikan rancangan. Contoh: izin dan sakit dianggap tidak hadir tetapi ada keterangan, dan tabel `users` untuk login.
3. Pekerjaan dipecah per bagian: SQL, data dummy, model, lalu satu controller API per tabel, route, dan terakhir view.
4. Untuk view, AI menyusun prompt lengkap (konteks, aturan bisnis, kontrak API, batasan) yang saya jalankan di OpenCode.
5. AI juga membuat README.md dan CLAUDE.md dari keputusan yang sudah kami sepakati.
## Keputusan yang saya ambil sendiri
- Membuat tabel langsung lewat SQL di phpMyAdmin, bukan migration.
- Memakai `web.php` untuk halaman dan `fetch` ke API lewat tombol.
- Auth memakai Breeze yang sudah terpasang, jadi tidak perlu controller auth tambahan.
- Izin dan sakit dihitung tidak hadir dalam persentase.
- Tailwind bawaan Breeze untuk tampilan.
## Cara saya mengecek hasil AI
- Mencocokkan rancangan dengan ketentuan tugas, poin per poin.
- Menjalankan kode dan mengirim balik pesan error beserta isi file atau output `route:list` ke AI, bukan menebak.
- Melihat struktur folder dan route sebelum lanjut ke langkah berikutnya.
## Koreksi selama prosesnya
- AI sempat mengusulkan `AuthController` dan token Sanctum. Saya koreksi karena auth sudah ditangani Breeze, lalu usulan itu dibuang.
- Error `Call to undefined method RekapController::index()` muncul. File `routes/api.php` saya ternyata belum sesuai rancangan (route resource yang tidak cocok dengan method controller dan parameter `{kela}`). AI membantu mendiagnosis dan memperbaikinya.
- Saya meminta nama route diberi awalan `api.` agar tidak bentrok dengan route web.
## Batasan penggunaan AI
AI tidak mengubah file saya secara langsung dan tidak menjalankan perintah di mesin saya. Perubahan skema, aturan bisnis, dan penambahan paket harus melalui persetujuan saya.
 