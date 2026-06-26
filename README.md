# Aplikasi Pengelolaan Keuangan Masjid Luqmanul Hakim

Aplikasi web pengelolaan keuangan untuk Masjid Luqmanul Hakim, Politeknik Negeri Bandung. dengan tambahan modul pencatatan transaksi, siklus akuntansi lengkap, dan pelaporan keuangan sesuai standar **ISAK 335** untuk entitas nirlaba.

## Tautan

- 🌐 [Aplikasi (Produksi)](https://mosque-app.thankfulpebble-09e6c7c4.southeastasia.azurecontainerapps.io)
- 📁 [Repositori](https://github.com/KoTA-204/mosque-app)

---

## Fitur Utama

### Manajemen Pengguna & Hak Akses
- Autentikasi berbasis session dengan enkripsi
- Role-Based Access Control (RBAC) untuk 5 peran pengguna: Ketua DKM, Bendahara 1, Bendahara 2, PHM, dan Panitia Kegiatan
- Setiap pengguna hanya dapat mengakses fitur sesuai tanggung jawabnya

### Master Data
- Manajemen kategori transaksi
- Manajemen chart of account
- Manajemen kegiatan khusus
- Manajemen aset

### Pencatatan Transaksi
- Input transaksi pemasukan dan pengeluaran dengan mapping akun debit dan kredit
- Impor mutasi rekening bank secara massal
- Unggah bukti transaksi berupa gambar dan PDF 

### Siklus Akuntansi 
- Jurnal pembuka
- Jurnal umum
- Buku besar
- Neraca saldo
- Jurnal penyesuaian
- Jurnal penutup

### Pelaporan Keuangan ISAK 335
- Laporan posisi keuangan
- Laporan penghasilan komperhensif
- Laporan aset neto
- Laporan arus kas
- Catatan atas laporan keuangan

### Akses Publik (Tanpa Login)
- Landing page
- Dashboard keuangan publik 

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Framework | Laravel 12 |
| Bahasa | PHP 8.3 |
| Database | PostgreSQL 16 |
| Frontend | TailAdmin (Tailwind CSS v4 + Alpine.js) |
| Build Tool | Vite |

---

## Persyaratan Sistem

Pastikan environment pengembangan memenuhi persyaratan berikut:

- **PHP 8.3+** dengan ekstensi `pdo`, `pdo_pgsql`, `gd`, `xml`, `bcmath`
- **Composer** (PHP dependency manager)
- **Node.js 11+** dan **npm**
- **PostgreSQL 18**

Verifikasi instalasi:
```bash
php -v
composer -V
node -v
npm -v
psql --version
```

---

## Instalasi dan Menjalankan Aplikasi

### 1. Clone Repositori

```bash
git clone https://github.com/KoTA-204/mosque-app.git
cd mosque-app
```

### 2. Instal Dependensi PHP

```bash
composer install
```

### 3. Instal Dependensi Node.js

```bash
npm install
```

### 4. Konfigurasi Environment

Salin file environment dan sesuaikan konfigurasi:

```bash
cp .env.example .env
```

Buka file `.env` dan sesuaikan konfigurasi berikut:

```env
APP_NAME="Keuangan Masjid Luqmanul Hakim"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mosque_app
DB_USERNAME=your_username
DB_PASSWORD=your_password

FILESYSTEM_DISK=local
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Buat Database dan Jalankan Migrasi

Buat database PostgreSQL terlebih dahulu:

```bash
createdb mosque_app
```

Kemudian jalankan migrasi dan seeder:

```bash
php artisan migrate
php artisan db:seed
```

### 7. Buat Storage Link

```bash
php artisan storage:link
```

### 8. Jalankan Aplikasi

```bash
composer run dev
```

Perintah ini akan menjalankan secara bersamaan:
- Laravel development server di `http://localhost:8000`
- Vite dev server untuk hot module reloading
- Queue worker untuk background jobs

---

## Peran Pengguna

| Peran | Akses |
|---|---|
| Ketua DKM | Seluruh fitur termasuk laporan keuangan ISAK 335 dan manajemen pengguna |
| Bendahara 1 | Transaksi infak, zakat, dan pengeluaran rutin |
| Bendahara 2 | Transaksi kencleng dan pengeluaran operasional |
| PHM | Pencatatan dana kencleng |
| Panitia Kegiatan | Transaksi dana kegiatan khusus |
| Publik (tanpa login) | Dashboard keuangan publik dan jadwal shalat |

---

## Struktur Proyek

```
mosque-app/
├── app/
│   ├── Helpers/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Observers/
│   ├── Providers/
│   ├── Services/
│   └── View/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── components/
│       ├── landing/
│       │   └── index.blade.php        
│       ├── layouts/
│       │   ├── app-header.blade.php
│       │   ├── app.blade.php
│       │   ├── backdrop.blade.php
│       │   ├── footer.blade.php
│       │   ├── guest.blade.php
│       │   ├── header.blade.php
│       │   ├── landing.blade.php
│       │   └── sidebar.blade.php
│       ├── pages/
│       │   ├── approval/
│       │   ├── auth/
│       │   ├── coa/
│       │   ├── dashboard/
│       │   ├── errors/
│       │   ├── kategori-transaksi/
│       │   ├── kegiatan/
│       │   ├── kencleng/
│       │   ├── menus/
│       │   ├── permissions/
│       │   ├── roles/
│       │   ├── transaksi/
│       │   ├── transaksi-kegiatan/
│       │   └── users/
│       ├── partials/
│       │   ├── step-dots.blade.php
│       │   └── stepper.blade.php
│       └── vendor/
│           ├── mail/
│           └── notifications/
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
└── vendor/

```

---

## Perintah Artisan yang Sering Digunakan

```bash
# Jalankan migrasi
php artisan migrate

# Jalankan migrasi dari awal beserta seeder
php artisan migrate:fresh --seed

# Bersihkan seluruh cache
php artisan optimize:clear

# Cache seluruh konfigurasi untuk produksi
php artisan optimize

# Lihat daftar route
php artisan route:list

# Jalankan queue worker
php artisan queue:work
```

---

## Troubleshooting

**Error koneksi database PostgreSQL**
- Pastikan PostgreSQL sudah berjalan dan database `mosque_app` sudah dibuat
- Periksa kembali kredensial pada file `.env`
- Pastikan ekstensi `pdo_pgsql` sudah aktif pada instalasi PHP

**Error permission pada direktori storage**
```bash
chmod -R 775 storage bootstrap/cache
```

**Error "Class not found"**
```bash
composer dump-autoload
```

**Error pada build npm**
```bash
rm -rf node_modules package-lock.json
npm install
```

**Bersihkan semua cache**
```bash
php artisan optimize:clear
```

---

## Lisensi

Aplikasi ini dikembangkan sebagai bagian dari tugas akhir Politeknik Negeri Bandung. Template dasar menggunakan [TailAdmin Laravel](https://tailadmin.com/license) — lihat halaman lisensi TailAdmin untuk informasi lebih lanjut.
