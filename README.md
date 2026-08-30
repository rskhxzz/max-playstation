# Maxibox Playstation

Website pemesanan dan penyewaan PlayStation berbasis Laravel — antar ke lokasi customer.

---

## Deskripsi Project

**Maxibox Playstation** adalah platform pemesanan sewa PlayStation 4 dengan pengiriman ke rumah. Customer dapat memesan secara online, memilih paket sewa, menentukan lokasi via Google Maps, dan membayar menggunakan QRIS (Midtrans). Tersedia dashboard Admin dan Driver.

---

## Requirement

| Komponen | Versi |
|----------|-------|
| PHP | 8.3+ (diuji pada 8.5) |
| Laravel | 13.x |
| PostgreSQL | 17.x |
| Composer | 2.x |
| Node.js | (opsional, untuk Vite) |

---

## Cara Instalasi

```bash
# 1. Clone atau masuk ke direktori project
cd max-playstation

# 2. Install PHP dependencies
composer install

# 3. Salin file environment
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Jalankan seeder data awal
php artisan db:seed --class=Database\\Seeders\\InitialDataSeeder
```

---

## Cara Mengatur `.env`

Edit file `.env` dan isi variabel berikut:

```env
APP_NAME="Maxibox Playstation"
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

# PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=maxibox_playstation
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Midtrans
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_NOTIFICATION_URL=https://yourdomain.com/webhooks/midtrans

# Google Maps
GOOGLE_MAPS_BROWSER_KEY=AIzaSy...
GOOGLE_MAPS_SERVER_KEY=AIzaSy...
```

---

## Konfigurasi PostgreSQL

1. Pastikan PostgreSQL sudah berjalan
2. Buat database: `CREATE DATABASE maxibox_playstation;`
3. Semua tabel sudah tersedia — **jangan jalankan migration destructive**
4. Isi kredensial di `.env`

---

## Konfigurasi Midtrans Sandbox

1. Daftar di [sandbox.midtrans.com](https://sandbox.midtrans.com)
2. Masuk ke **Settings → Access Keys**
3. Salin **Server Key** dan **Client Key** (sandbox)
4. Isi `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY`
5. Set `MIDTRANS_IS_PRODUCTION=false` untuk sandbox

---

## Konfigurasi Webhook Midtrans

1. Di dashboard Midtrans, buka **Settings → Configuration**
2. Isi **Payment Notification URL**: `https://yourdomain.com/webhooks/midtrans`
3. Jika lokal, gunakan ngrok: `ngrok http 8000`
4. Isi `MIDTRANS_NOTIFICATION_URL` dengan URL yang dapat diakses publik

---

## Konfigurasi Google Maps

### API yang perlu diaktifkan di Google Cloud Console:
- **Maps JavaScript API** (untuk peta di frontend)
- **Places API** (untuk autocomplete alamat)
- **Routes API** (untuk kalkulasi jarak di backend)

### Dua jenis API Key:
| Key | Variabel | Pembatasan |
|-----|----------|------------|
| Browser Key | `GOOGLE_MAPS_BROWSER_KEY` | Batasi dengan HTTP referrer (domain Anda) + Maps JS API + Places API |
| Server Key | `GOOGLE_MAPS_SERVER_KEY` | Batasi dengan IP server + Routes API |

> **Keamanan**: Jangan expose `GOOGLE_MAPS_SERVER_KEY` ke frontend.

---

## Cara Menjalankan Project

```bash
php artisan serve
# Buka http://localhost:8000
```

---

## Cara Menjalankan Scheduler

Scheduler memeriksa pembayaran expired setiap menit.

### Development (manual)
```bash
php artisan schedule:work
```

### Production (Crontab)
Tambahkan ke crontab server:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Jalankan manual
```bash
php artisan payments:expire
```

---

## Cara Menjalankan Test

```bash
# Semua test
php artisan test

# Filter test tertentu
php artisan test --filter=MaxiboxTest

# Verbose
php artisan test --filter=MaxiboxTest
```

> Test menggunakan database PostgreSQL yang sama dengan transaksi yang di-rollback (`DatabaseTransactions`).

---

## Akun Admin dan Driver

Setelah menjalankan seeder:

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Driver | `driver` | `driver123` |

**Halaman login**: `http://localhost:8000/login`

---

## Alur Pembayaran DP

1. Customer memilih **Bayar DP Rp50.000**
2. Customer membayar Rp50.000 via QRIS → `payment_status = partial`
3. Booking statusnya `delivered` (Siap Diantar)
4. Driver membuka detail pesanan → tekan **Buat QRIS Pelunasan**
5. Customer membayar sisa tagihan via QRIS
6. Setelah lunas, Driver menekan **Selesai Mengantar**
7. Booking statusnya `arrived`

---

## Alur Pembayaran Lunas

1. Customer memilih **Bayar Lunas**
2. Customer membayar total tagihan via QRIS → `payment_status = paid`
3. Booking statusnya `delivered` (Siap Diantar)
4. Driver mengantarkan → tekan **Selesai Mengantar**
5. Booking statusnya `arrived`

---

## Daftar Status Booking

| Status Database | Label UI | Keterangan |
|-----------------|----------|------------|
| `pending_payment` | Menunggu Pembayaran | Menunggu pembayaran awal |
| `delivered` | Siap Diantar | Pembayaran diterima, siap diantar |
| `arrived` | Sudah Sampai | PlayStation sudah sampai di customer |
| `completed` | Selesai | Sewa selesai |
| `canceled` | Dibatalkan | Dibatalkan oleh Admin atau sistem |
| `expired` | Kedaluwarsa | Pembayaran tidak diselesaikan dalam 60 menit |
| `delivery_failed` | Pengantaran Gagal | Pengantaran gagal |

---

## Daftar Status Pembayaran

| Status Database | Label UI | Keterangan |
|-----------------|----------|------------|
| `unpaid` | Belum Dibayar | Belum ada pembayaran |
| `partial` | Sudah DP | Sudah bayar DP, ada sisa |
| `paid` | Lunas | Total tagihan sudah lunas |
| `refunded` | Dikembalikan | Dana dikembalikan |

---

## Struktur Route

| Path | Deskripsi |
|------|-----------|
| `GET /` | Landing page |
| `GET /pesan` | Form pemesanan |
| `POST /pesan` | Proses pemesanan |
| `GET /pesanan/{code}/pembayaran` | Halaman pembayaran |
| `GET /pesanan/{code}/status` | Status pesanan |
| `POST /api/calculate-delivery` | API kalkulasi ongkos kirim |
| `POST /webhooks/midtrans` | Webhook Midtrans |
| `GET /login` | Halaman login |
| `GET /admin/dashboard` | Dashboard Admin |
| `GET /driver/dashboard` | Dashboard Driver |

---

## Catatan Penting

- Jangan jalankan `php artisan migrate:fresh`, `migrate:reset`, atau `db:wipe`
- Webhook Midtrans dikecualikan dari CSRF verification
- Rate limiting aktif pada: login (5/menit), booking (10/menit), kalkulasi jarak (30/menit)
- Signature Midtrans diverifikasi setiap webhook masuk
- Jika `GOOGLE_MAPS_BROWSER_KEY` kosong, form booking tetap bisa diisi dengan koordinat manual (mode development)
