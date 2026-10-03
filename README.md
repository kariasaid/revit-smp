# REVIT SMP — Sistem Informasi Bantuan Revitalisasi Sekolah

Aplikasi web berbasis **CodeIgniter 4** + **Bootstrap 5** + **Chart.js** + **MySQL** untuk monitoring pelaksanaan revitalisasi sekolah (SMP).

## Fitur Utama

| Halaman | Route | Keterangan |
|---------|-------|------------|
| Login | `/login` | Autentikasi email/username + password |
| Dashboard | `/dashboard` | Profil pengawas + daftar sekolah kelolaan |
| Sekolah dan Penugasan | `/admin/sekolah` | Admin mengelola sekolah, jenis bantuan, dan akun perencana/pengawas |
| Keuangan Admin | `/admin/keuangan/buku-bank`, `/admin/keuangan/buku-kas-tunai`, `/admin/keuangan/buku-kas-umum`, `/admin/keuangan/bahan-bangunan`, `/admin/keuangan/ongkos-tukang` | Pencatatan buku keuangan, bahan bangunan, dan ongkos tukang per sekolah |
| Tim P2SP | `/tim-p2sp` | Admin dan perencana mengelola enam anggota, identitas, jabatan, dan tanda tangan tiap sekolah |
| Verifikasi Time Schedule | `/admin/verifikasi-time-schedule` | Admin menerima/menolak jadwal awal perencana sebelum progres diinput |
| Kurva S Pelaksanaan | `/pelaksanaan/kurva-s` | Admin melihat Kurva S tiap sekolah; pengawas melihat sekolah kelolaannya |
| Time Schedule Awal | `/perencana/time-schedule` | Perencana membuat rencana tanggal dan target fisik mingguan |
| Monitoring Progres | `/perencana/monitoring-progres` | Perencana melihat jadwal dan status laporan progres per minggu |
| Progres Pelaksanaan | `/pelaksanaan/progres` | Pengawas mengisi progres mingguan dan melihat status validasi |
| Validasi Progres | `/validasi/progres` | Admin menerima atau menolak progres yang diajukan |
| Kurva S | `/pelaksanaan/kurva-s` | Line chart Rencana vs Realisasi + tabel indikator |
| Pelaporan 50% / 100% | `/pelaporan/50`, `/pelaporan/100` | Upload dokumen pelaporan |
| Profil Pengguna | `/profil` | Ubah biodata & foto |

## Persyaratan

- PHP 8.1+
- MySQL 5.7+ / MariaDB (XAMPP)
- Composer
- Extensi PHP: `intl`, `mbstring`, `json`, `mysqlnd`

## Menjalankan di XAMPP

### 1. Database

```bash
# Buka phpMyAdmin atau terminal MySQL
mysql -u root < schema.sql
```

Atau import file `schema.sql` melalui phpMyAdmin.

Database: `revit_smp`

Untuk database yang sudah digunakan sebelum fitur jenis bantuan tersedia, jalankan migrasi dari folder project:

```bash
php spark migrate
```

Migrasi juga membuat tabel `tim_p2sp` untuk menyimpan anggota dan tanda tangan digital Tim P2SP.

### 2. Install dependensi

```bat
cd /d C:\xampp\htdocs\revit-smp
composer install
```

Project ini sudah berisi bootstrap CodeIgniter dan tidak perlu dibuat ulang atau disalin ke folder lain.

### 3. Konfigurasi

Konfigurasi default memakai MySQL XAMPP (`root`, tanpa password), database `revit_smp`, dan URL `http://localhost/revit-smp/`. Sesuaikan `app/Config/Database.php` bila kredensial MySQL berbeda. Aktifkan Apache `mod_rewrite` dan izinkan `AllowOverride All` agar `.htaccess` berfungsi.

### 4. Folder Upload

Folder upload sudah disiapkan. Pada Windows/XAMPP, pastikan akun yang menjalankan Apache memiliki izin tulis ke `writable/uploads` dan `public/uploads`.

### 5. Buka aplikasi

Jalankan Apache dan MySQL dari XAMPP, lalu buka `http://localhost/revit-smp/`. Untuk development server CodeIgniter, jalankan `php spark serve` dari folder project dan buka URL yang ditampilkan.

### 6. Akun Demo

| Role | Username / Email | Password |
|------|------------------|----------|
| Pengawas | `meindrawan` atau `5108060105820014@email.com` | `password` |
| Admin | `admin` | `password` |
| Perencana | `perencana` | `RencanaSMP#2026` |

Saat admin menambahkan sekolah, username dan password awal perencana serta pengawas menggunakan NIK masing-masing. Password disimpan dalam bentuk hash; pengguna disarankan menggantinya melalui halaman profil setelah login.

> Password di-hash dengan `password_hash()` (bcrypt). Default dummy: **password**

## Struktur Folder yang Disediakan

```
revit-smp/
├── schema.sql
├── README.md
└── app/
    ├── Config/
    │   └── Routes.php
    ├── Controllers/
    │   ├── Auth.php
    │   ├── Dashboard.php
    │   ├── Pelaksanaan.php
    │   ├── Pelaporan.php
    │   └── Profil.php
    ├── Filters/
    │   └── AuthFilter.php
    ├── Models/
    │   ├── UserModel.php
    │   ├── SekolahModel.php
    │   ├── ProgresMingguanModel.php
    │   ├── DokumenPelaporanModel.php
    │   └── PersonilSekolahModel.php
    └── Views/
        ├── layouts/main.php
        ├── auth/login.php
        ├── dashboard/index.php
        ├── pelaksanaan/
        │   ├── progres.php
        │   └── kurva_s.php
        ├── pelaporan/index.php
        └── profil/index.php
```

## Catatan Implementasi

1. **Kurva S** menggunakan Chart.js 4 (CDN). Data akumulasi dihitung di `ProgresMingguanModel::getKurvaS()`.
2. **Deviasi** = Realisasi Fisik − Target Rencana (per minggu & kumulatif).
3. **Pelaporan 100%** terkunci jika akumulasi fisik < 100%.
4. UI mengikuti desain screenshot: warna primer `#1d5296`, sidebar kiri, card modern, badge hijau/merah untuk deviasi.
5. Filter `auth` melindungi semua route kecuali login/logout.
6. Progres yang dikirim pengawas berstatus **Diajukan** dan baru dihitung dalam Kurva S setelah admin mengubah status menjadi **Diterima**. Progres yang ditolak dapat diperbaiki dan diajukan kembali.
7. Setiap laporan progres baru wajib menyertakan foto tampak depan, tampak belakang, dan bagian dalam bangunan. Foto menerima JPG, PNG, atau WebP hingga 12 MB sebelum kompresi dan otomatis disimpan sebagai JPEG maksimal 5 MB.
8. Akun ber-role `perencana` hanya dapat menyusun jadwal untuk sekolah yang nama perencananya tercatat sama pada data personil sekolah. Target rencana total harus 100% dan jadwal terkunci setelah progres mulai dilaporkan.
9. Untuk proyek yang sudah berjalan, target dari laporan mingguan yang ada akan dipakai sebagai saran awal jadwal; sisa target dibagi ke minggu berikutnya dan tanggal periode tetap diisi oleh perencana.
10. Perencana hanya memasukkan tanggal mulai Minggu 1; tanggal minggu berikutnya dibuat otomatis dalam periode tujuh hari.
11. Time schedule berstatus **Diajukan** setelah dikirim perencana. Admin harus menerima jadwal sebelum pengawas dapat menginput progres; jadwal yang ditolak dapat direvisi dan diajukan ulang.
12. Tim P2SP terdiri dari Penanggung Jawab (Kepala Sekolah), Ketua P2SP, Bendahara, Sekretaris, Kepala Pelaksana, dan Keamanan. Setiap anggota wajib memiliki nama, NIP/NIK, jabatan, dan gambar tanda tangan.

## Pengembangan Lanjutan (opsional)

- CRUD Adendum
- Export PDF Kurva S (dompdf / TCPDF)
- Multi-sekolah switcher di topbar
- Notifikasi realtime status verval
- Role-based access control (RBAC) lebih detail
