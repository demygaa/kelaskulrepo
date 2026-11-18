# Aplikasi Absensi Mahasiswa

Aplikasi web untuk mencatat dan merekap kehadiran mahasiswa per kelas. Dibuat untuk dosen yang selama ini mencatat kehadiran di kertas, sehingga rekap di akhir semester sering salah dan lambat.

Aplikasi ini fullstack: antarmuka pengguna (frontend), backend (REST API), dan database.

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | Laravel (PHP), REST API di `routes/api.php` |
| Database | MySQL (dikelola lewat phpMyAdmin) |
| Autentikasi | Laravel Breeze (stack Blade, berbasis session) |
| Frontend | Blade, Tailwind CSS, JavaScript biasa (`fetch` ke API), Vite |

## Fitur

1. Kelola data **kelas** (nama mata kuliah, nama kelas, dosen): tambah, lihat, ubah, hapus.
2. Kelola data **mahasiswa** (NIM, nama): tambah, lihat, ubah, hapus, serta pencarian berdasarkan NIM atau nama.
3. **Mendaftarkan mahasiswa ke kelas** (bisa banyak sekaligus) dan mengeluarkannya.
4. Membuat **pertemuan** untuk sebuah kelas (pertemuan ke-berapa dan tanggalnya).
5. **Mencatat kehadiran** setiap mahasiswa di setiap pertemuan dengan status: `hadir`, `izin`, `sakit`, atau `alpa`. Input dilakukan dalam satu layar untuk seluruh mahasiswa kelas.
6. **Rekap kehadiran** per kelas: jumlah tiap status dan persentase kehadiran setiap mahasiswa.
7. Login dosen (semua halaman dilindungi autentikasi).

## Aturan Bisnis

1. Hanya mahasiswa yang **terdaftar di kelas tersebut** yang dapat dicatat kehadirannya. Aturan ini dijaga di dua lapis: relasi database (`kehadirans` menunjuk ke `kelas_mahasiswa`) dan pengecekan di `KehadiranController`.
2. Status kehadiran hanya boleh salah satu dari empat: `hadir`, `izin`, `sakit`, `alpa` (enum di database dan validasi di API).
3. Data tidak boleh ganda: NIM unik, mahasiswa tidak bisa didaftarkan dua kali ke kelas yang sama, nomor pertemuan unik per kelas, dan satu mahasiswa hanya punya satu catatan kehadiran per pertemuan.

### Aturan izin dan sakit

**Izin dan sakit dihitung sebagai tidak hadir.** Keduanya tidak menambah persentase kehadiran, tetapi tetap tercatat sebagai status tersendiri dan **wajib disertai keterangan** (misalnya "surat dokter" atau "acara keluarga"). Status `hadir` dan `alpa` tidak memerlukan keterangan.

### Rumus persentase kehadiran

```
persentase = jumlah pertemuan berstatus "hadir" ÷ total pertemuan di kelas × 100
```

- Penyebut adalah **total pertemuan yang sudah dibuat** di kelas, bukan hanya yang sudah diisi. Pertemuan yang belum diinput kehadirannya tetap masuk penyebut sehingga menurunkan persentase.
- Jika kelas belum punya pertemuan, persentase ditampilkan 0 (tidak terjadi pembagian dengan nol).
- Di halaman rekap, persentase di bawah 75% ditandai merah, 75-89% kuning, dan 90% ke atas hijau.

### Catatan tentang status `belum_diisi`

`belum_diisi` hanya **status tampilan** untuk mahasiswa yang belum punya catatan di suatu pertemuan. Status ini tidak pernah disimpan di database dan ditolak oleh API jika dikirim. Jadi status yang tersimpan tetap hanya empat.

## Rancangan Database

```
users (akun login dosen)

kelas ──< kelas_mahasiswa >── mahasiswas
  │               │
  │               └──< kehadirans >── pertemuans
  └──────────────────────────────────< (kelas_id)
```

| Tabel | Fungsi | Kolom utama |
|---|---|---|
| `users` | Akun login | `name`, `email`, `password` |
| `kelas` | Data kelas | `nama_mata_kuliah`, `nama_kelas`, `dosen` |
| `mahasiswas` | Data mahasiswa | `nim` (unik), `nama` |
| `kelas_mahasiswa` | Pendaftaran mahasiswa ke kelas | `kelas_id`, `mahasiswa_id` (unik berpasangan) |
| `pertemuans` | Pertemuan per kelas | `kelas_id`, `pertemuan_ke`, `tanggal` (`kelas_id` + `pertemuan_ke` unik) |
| `kehadirans` | Kehadiran per mahasiswa per pertemuan | `pertemuan_id`, `kelas_mahasiswa_id`, `status`, `keterangan` (`pertemuan_id` + `kelas_mahasiswa_id` unik) |

Seluruh foreign key memakai `ON DELETE CASCADE`: menghapus kelas ikut menghapus pertemuan, pendaftaran, dan kehadiran terkait.

## Cara Menjalankan

### Prasyarat

PHP 8.2+, Composer, Node.js dan npm, MySQL (misalnya lewat XAMPP/Laragon) dengan phpMyAdmin.

### Langkah

1. **Install dependensi**

   ```bash
   composer install
   npm install
   ```

2. **Siapkan environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Atur koneksi database di `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=absensi_kampus
   DB_USERNAME=root
   DB_PASSWORD=

   SESSION_DRIVER=file
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   ```

3. **Buat database dan impor skema.** Tabel dibuat manual lewat SQL, jadi **jangan menjalankan `php artisan migrate`**.

   - Di phpMyAdmin, buat database `absensi_kampus` dengan collation `utf8mb4_unicode_ci`.
   - Pilih database tersebut, buka tab **Import** (atau tab **SQL**), lalu jalankan berurutan:
     1. `database/schema.sql` (struktur tabel)
     2. `database/dummy.sql` (data contoh, opsional)

4. **Buat akun login.** Kata sandi harus di-hash oleh Laravel, jadi gunakan tinker:

   ```bash
   php artisan tinker
   >>> App\Models\User::create(['name'=>'Dosen','email'=>'dosen@kampus.test','password'=>Hash::make('password123')]);
   ```

5. **Jalankan aplikasi** (dua terminal):

   ```bash
   php artisan serve     # terminal 1
   npm run dev           # terminal 2, biarkan berjalan
   ```

   Untuk produksi atau pengumpulan, cukup jalankan `npm run build` sekali.

6. Buka `http://localhost:8000` dan login dengan akun di langkah 4.

### Akun contoh

| Email | Password |
|---|---|
| `dosen@kampus.test` | `password123` |

## Halaman Aplikasi

| Halaman | URL | Fungsi |
|---|---|---|
| Kelola kelas | `/kelas` | Daftar, tambah, ubah, hapus kelas |
| Kelola mahasiswa | `/mahasiswas` | Daftar, cari, tambah, ubah, hapus mahasiswa |
| Peserta kelas | `/kelas/{id}/peserta` | Daftarkan dan keluarkan mahasiswa dari kelas |
| Daftar pertemuan | `/kelas/{id}/pertemuans` | Buat, ubah, hapus pertemuan |
| Input kehadiran | `/pertemuans/{id}/kehadiran` | Catat kehadiran seluruh mahasiswa dalam satu layar |
| Rekap | `/kelas/{id}/rekap` | Jumlah tiap status dan persentase kehadiran |

Alur penggunaan: **Kelas** → (Peserta | Pertemuan | Rekap) → dari Pertemuan, buka **Input Kehadiran**.

## Endpoint API

Semua endpoint berawalan `/api` dan mengembalikan JSON. Kirim header `Accept: application/json`.

| Method | Endpoint | Fungsi |
|---|---|---|
| GET, POST | `/api/kelas` | Daftar, tambah kelas |
| GET, PUT, DELETE | `/api/kelas/{kelas}` | Detail, ubah, hapus kelas |
| GET, POST | `/api/mahasiswas` | Daftar (`?search=`), tambah mahasiswa |
| GET, PUT, DELETE | `/api/mahasiswas/{mahasiswa}` | Detail, ubah, hapus mahasiswa |
| GET, POST | `/api/kelas/{kelas}/mahasiswas` | Daftar peserta, daftarkan mahasiswa (`mahasiswa_ids`) |
| DELETE | `/api/kelas/{kelas}/mahasiswas/{mahasiswa}` | Keluarkan mahasiswa dari kelas |
| GET, POST | `/api/kelas/{kelas}/pertemuans` | Daftar, tambah pertemuan |
| GET, PUT, DELETE | `/api/pertemuans/{pertemuan}` | Detail, ubah, hapus pertemuan |
| GET, PUT | `/api/pertemuans/{pertemuan}/kehadirans` | Lihat dan simpan kehadiran (bulk) |
| GET | `/api/kelas/{kelas}/rekap` | Rekap kehadiran kelas |

### Contoh body input kehadiran (bulk)

```http
PUT /api/pertemuans/2/kehadirans
```

```json
{
  "kehadirans": [
    { "kelas_mahasiswa_id": 1, "status": "hadir" },
    { "kelas_mahasiswa_id": 2, "status": "izin", "keterangan": "Acara keluarga" },
    { "kelas_mahasiswa_id": 3, "status": "sakit", "keterangan": "Surat dokter" }
  ]
}
```

- Mengirim ulang untuk pertemuan yang sama berarti **koreksi** (data lama diperbarui, tidak dobel).
- `keterangan` wajib untuk status `izin` dan `sakit`, dan otomatis dikosongkan untuk `hadir` dan `alpa`.
- Semua baris disimpan dalam satu transaksi: jika satu gagal, tidak ada yang tersimpan.
- `kelas_mahasiswa_id` harus milik kelas dari pertemuan tersebut, kalau tidak API mengembalikan 422.

### Contoh response rekap

```json
{
  "kelas": { "id": 1, "nama_mata_kuliah": "Pemrograman Web", "nama_kelas": "TI-3A", "dosen": "Dr. Budi Santoso" },
  "total_pertemuan": 3,
  "rata_rata_hadir": 22.22,
  "mahasiswas": [
    {
      "nim": "2023001", "nama": "Andi Pratama",
      "hadir": 2, "izin": 0, "sakit": 0, "alpa": 0, "belum_diisi": 1,
      "persentase_hadir": 66.67,
      "catatan_izin_sakit": []
    }
  ]
}
```

## Pengujian Cepat

Setelah mengimpor `database/dummy.sql`, beberapa skenario yang bisa dicoba (Postman atau lewat antarmuka):

| Skenario | Hasil yang diharapkan |
|---|---|
| Input kehadiran pertemuan 2 untuk seluruh peserta kelas 1 | Berhasil |
| Input ulang pertemuan yang sama | Berhasil, data diperbarui, tidak dobel |
| Status `izin` atau `sakit` tanpa keterangan | Ditolak (422) |
| Status di luar empat yang valid (misalnya `telat`) | Ditolak (422) |
| Mencatat mahasiswa yang tidak terdaftar di kelas pertemuan | Ditolak (422) |
| Menambah mahasiswa dengan NIM yang sudah ada | Ditolak (422) |
| Membuat pertemuan dengan nomor yang sudah ada di kelas yang sama | Ditolak (422) |
| Mendaftarkan mahasiswa yang sama dua kali ke satu kelas | Tidak dobel, dilaporkan sudah terdaftar |

## Struktur Kode Utama

```
app/
├── Http/Controllers/
│   ├── PageController.php              # menampilkan halaman (view)
│   └── Api/
│       ├── KelasController.php
│       ├── MahasiswaController.php
│       ├── KelasMahasiswaController.php
│       ├── PertemuanController.php
│       ├── KehadiranController.php
│       └── RekapController.php
└── Models/                             # Kelas, Mahasiswa, KelasMahasiswa, Pertemuan, Kehadiran, User
database/
├── schema.sql                          # struktur tabel
└── dummy.sql                           # data contoh
public/js/api.js                        # helper fetch ke API
resources/views/                        # kelas, mahasiswa, pertemuan, kehadiran, rekap
routes/
├── web.php                             # route halaman (dilindungi auth)
└── api.php                             # route API
```
