# CafeFlow POS - Cafe POS & Management System

**CafeFlow POS** adalah sistem aplikasi kasir (*Point of Sale*) dan manajemen penjualan kafe modern, responsif, dan siap produksi. Dibangun menggunakan arsitektur **MVC murni (Pure PHP 8+)**, **MySQL/MariaDB**, **Bootstrap 5**, **Chart.js**, serta integrasi pembayaran **QRIS Dinamis resmi Midtrans** dengan verifikasi tanda tangan digital SHA512 dan perlindungan idempoten.

---

## 🌟 Fitur Utama

1. **Autentikasi & Role-Based Access Control (RBAC):**
   - **Admin / Owner**: Akses penuh ke dashboard omzet & laba, katalog produk, resep & bahan baku, transaksi, pengeluaran, laporan keuangan komprehensif, manajemen staf, dan pengaturan sistem.
   - **Kasir**: Antarmuka kasir cepat (POS), pencarian menu instan, pemilihan dine-in (nomor meja) atau takeaway, pembayaran tunai (kalkulator kembalian otomatis), QRIS dinamis, dan cetak struk thermal 58mm/80mm.
   - **Dapur / Barista**: *Kitchen Display System* (KDS) live dengan auto-sync tanpa reload halaman, menampilkan tiket pesanan masuk, daftar item, catatan khusus per menu (misal: *less sugar, no ice*), dan perubahan status pesanan (*Menunggu* &rarr; *Diproses* &rarr; *Siap Saji* &rarr; *Selesai*).

2. **Dashboard Finansial Real-time (Admin):**
   - Kartu statistik (KPI): Omzet bruto hari ini/periode, total transaksi selesai, penjualan tunai, penjualan QRIS terverifikasi gateway, pengeluaran operasional, dan estimasi laba bersih.
   - Grafik interaktif tren penjualan harian (7 hari terakhir) dan bulanan menggunakan Chart.js.
   - Peringatan dini stok menipis (*Low Stock Alert*) untuk bahan baku mentah dan produk langsung.
   - Daftar transaksi terkini dengan status badge dinamis.
   - Filter rentang tanggal fleksibel (Hari ini, 7 hari terakhir, Bulan ini, Custom date range).

3. **Katalog Produk & Resep Bahan Baku (*Recipe Inventory Deduction*):**
   - CRUD Produk: SKU, Nama, Kategori, Foto, Harga Jual, HPP (Modal), Satuan, Deskripsi, Status Ketersediaan.
   - Dua mode manajemen stok:
     - **Resep Bahan Baku**: Pengurangan otomatis stok bahan mentah (misal: 1 cup Kopi Susu = 18g espresso beans + 140ml fresh milk + 25ml gula aren) secara transaksional saat transaksi lunas.
     - **Stok Langsung**: Pengurangan stok per unit untuk pastry/kue/produk botolan.
   - Riwayat mutasi stok (*Inventory Movements*) mencatat setiap pergerakan (IN, OUT, SALE, ADJUSTMENT) lengkap dengan referensi Order ID dan penanggung jawab.

4. **Point of Sale (POS Kasir) yang Cepat & Modern:**
   - Visual katalog berbasis kartu dengan foto, filter kategori (*Coffee*, *Non-Coffee*, *Makanan Berat*, *Snacks*, *Pastry*), dan pencarian instan.
   - Keranjang belanja interaktif dengan pengaturan kuantitas, catatan khusus per item (*item notes*), diskon (persen atau nominal), pajak restoran (PPN 10%), dan biaya layanan.
   - Validasi harga dan total pembayaran di backend untuk mencegah manipulasi dari browser (*Never trust client data*).

5. **Pembayaran Tunai (Cash):**
   - Tombol pilihan nominal uang cepat (*Uang Pas, 20.000, 50.000, 100.000, 200.000, 500.000*).
   - Validasi uang masuk minimal sebesar total tagihan.
   - Perhitungan kembalian otomatis real-time.
   - Struk thermal printer 58mm / 80mm instan.

6. **Integrasi QRIS Dinamis Midtrans & Sandbox Simulator:**
   - Menghasilkan QR code pembayaran unik per transaksi sesuai nominal server.
   - Countdown timer kedaluwarsa QRIS (15 menit).
   - Real-time frontend polling memeriksa status pembayaran ke server setiap 3 detik.
   - Endpoint webhook aman (`/api/webhook/midtrans`) yang memverifikasi signature resmi:  
     $$\text{SHA512}(\text{order\_id} + \text{status\_code} + \text{gross\_amount} + \text{ServerKey})$$
   - Mekanisme **Idempotent Guard**: Memastikan notifikasi berulang dari gateway tidak menggandakan pemasukan kas atau memotong stok berkali-kali.
   - **Sandbox Developer Tool**: Tombol simulasi scan & bayar di dalam modal untuk pengujian end-to-end tanpa perlu aplikasi e-wallet sungguhan saat fase development.

7. **Modul Laporan Keuangan Lengkap:**
   - **Laporan Penjualan**: Omzet bruto, diskon, penjualan bersih, pajak, AOV, dan penjualan per menu/kategori (dengan ekspor CSV).
   - **Laporan Metode Pembayaran**: Rekapitulasi Cash vs QRIS terverifikasi vs Pending vs Gagal/Expired per tanggal.
   - **Laporan Laba Rugi (P&L Statement)**: Pendapatan Bersih - HPP (COGS) = Laba Kotor; Laba Kotor - Beban Operasional = Laba Bersih.
   - **Laporan Pengeluaran Operasional**: Pencatatan beban harian/bulanan (bahan baku, gaji, utilitas listrik/air, sewa, peralatan).
   - **Laporan Mutasi Stok**: Audit jejak keluar-masuk seluruh inventaris kafe.
   - Format cetak ramah printer (PDF/Print view) dengan CSS cetak khusus.

---

## 💻 Kredensial Akun Demo

| Peran (Role) | Email | Password | Hak Akses Utama |
|---|---|---|---|
| **Admin / Owner** | `admin@cafeflow.com` | `admin123` | Seluruh fitur: Dashboard, Produk, Stok, Pengeluaran, Laporan, Pengguna, Pengaturan |
| **Kasir** | `kasir@cafeflow.com` | `kasir123` | Akses POS, Keranjang, Transaksi Tunai/QRIS, Cetak Struk, Riwayat Pesanan |
| **Dapur / Barista** | `dapur@cafeflow.com` | `dapur123` | Kitchen Display System (KDS), Update Status Pesanan (*Mulai Buat*, *Siap Saji*, *Selesai*) |

> Di halaman login telah disediakan tombol **1-Click Demo Quick Fill** untuk mempermudah pengujian.

---

## 🛠️ Struktur Proyek (MVC Architecture)

```
penjualan_cafe/
├── app/
│   ├── Config/
│   │   └── Database.php          # Koneksi Singleton PDO MySQL
│   ├── Controllers/
│   │   ├── AuthController.php      # Login, Logout, RBAC redirection
│   │   ├── DashboardController.php # KPI Cards, Chart.js datasets, filter tanggal
│   │   ├── PosController.php       # Halaman kasir, filter menu, cart engine
│   │   ├── OrderController.php     # Manajemen pesanan, validasi harga server
│   │   ├── PaymentController.php   # Pembayaran tunai & pembuat tagihan QRIS
│   │   ├── WebhookController.php   # Verifikasi webhook Midtrans (SHA512 & Idempoten)
│   │   ├── KitchenController.php   # Kitchen Display System (KDS) live
│   │   ├── ProductController.php   # CRUD produk, harga jual, HPP, resep bahan
│   │   ├── IngredientController.php# CRUD bahan baku, restock, mutasi stok
│   │   ├── ExpenseController.php   # Pengeluaran operasional kafe
│   │   ├── ReportController.php    # Laporan Penjualan, Pembayaran, Laba Rugi, Stok
│   │   ├── UserController.php      # Manajemen akun staf & peran
│   │   └── SettingController.php   # Pengaturan kafe, PPN %, kredensial Midtrans
│   ├── Middleware/
│   │   ├── AuthMiddleware.php      # Proteksi otentikasi login
│   │   └── RoleMiddleware.php      # Pembatasan hak akses peran (Admin/Kasir/Dapur)
│   ├── Models/
│   │   ├── User.php, Product.php, Category.php, Ingredient.php, ProductRecipe.php
│   │   ├── Order.php, OrderItem.php, Payment.php, PaymentEvent.php, Expense.php
│   │   ├── ExpenseCategory.php, InventoryMovement.php, Table.php, Setting.php, Refund.php
│   ├── Services/
│   │   ├── MidtransService.php     # Dynamic QRIS charge, SHA512 signature checker
│   │   └── ReceiptService.php      # Pengolah data struk belanja thermal
│   ├── Views/                      # Antarmuka Bootstrap 5 & Plus Jakarta Sans
│   └── Helpers/
│       ├── Auth.php, Csrf.php, Env.php, Format.php, Logger.php, functions.php
├── core/
│   ├── Request.php                 # Parser request HTTP, JSON body, subfolder path normalizer
│   ├── Router.php                  # Router ekspresif dengan middleware pipeline
│   ├── Controller.php              # Base controller (JSON, redirect, render, validator)
│   ├── Model.php                   # Base model PDO dengan transaksi ACID
│   └── View.php                    # Layout view engine buffer
├── database/
│   ├── cafeflow_pos.sql            # Skema lengkap 16 tabel & data awal (seeder)
│   ├── migrate.php                 # Script CLI inisialisasi & seeder database
│   └── test_runner.php             # E2E integration test suite (22 assertions)
├── public/
│   ├── css/
│   │   ├── app.css                 # Desain modern warm cafe palette (krem, cokelat, hijau)
│   │   └── receipt.css             # Desain cetak struk thermal 58mm/80mm
│   ├── js/
│   │   ├── app.js
│   │   ├── pos.js                  # Engine kalkulasi keranjang, modal cash & QRIS poller
│   │   └── kitchen.js              # Polling real-time KDS tanpa reload
│   ├── images/products/            # Thumbnail aset menu kopi & kuliner
│   ├── .htaccess                   # Rewrite engine Apache ke index.php
│   └── index.php                   # Front controller aplikasi
├── .env.example                    # Template konfigurasi environment
├── .env                            # Konfigurasi aktif database & Midtrans
├── .htaccess                       # Root htaccess untuk XAMPP htdocs subfolder
└── README.md                       # Dokumentasi lengkap
```

---

## 🚀 Panduan Instalasi & Konfigurasi (XAMPP)

### Prasyarat
- XAMPP dengan **PHP 8.1+** (disarankan PHP 8.2) dan **MySQL/MariaDB**.
- Ekstensi PHP aktif: `pdo_mysql`, `curl`, `json`, `mbstring`, `openssl`.

### Langkah 1: Penempatan Berkas
Pastikan folder proyek berada di direktori `htdocs` XAMPP atau folder kerja Anda:
```
C:\xampp\htdocs\penjualan_cafe
```

### Langkah 2: Menjalankan MySQL
1. Buka **XAMPP Control Panel**.
2. Klik tombol **Start** pada modul **Apache** dan **MySQL**.

### Langkah 3: Konfigurasi Database
1. Buka terminal (PowerShell atau Command Prompt) di dalam direktori proyek:
   ```powershell
   cd "C:\xampp\htdocs\penjualan_cafe"
   ```
2. Jalankan script inisialisasi otomatis:
   ```powershell
   C:\xampp\php\php.exe database/migrate.php
   ```
   *Atau melalui phpMyAdmin:*
   - Buka `http://localhost/phpmyadmin/`
   - Buat database bernama `cafeflow_pos`
   - Klik tab **Import** dan pilih file `database/cafeflow_pos.sql`, lalu klik **Go**.

### Langkah 4: Konfigurasi File `.env`
Periksa file `.env` di root direktori proyek. Konfigurasi default XAMPP:
```ini
APP_NAME="CafeFlow POS"
APP_ENV=development
APP_DEBUG=true
APP_URL="http://localhost/penjualan_cafe"

# Koneksi Database XAMPP
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cafeflow_pos
DB_USERNAME=root
DB_PASSWORD=

# Kredensial Midtrans Sandbox
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY="SB-Mid-server-TEST-KEY-cafeflow"
MIDTRANS_CLIENT_KEY="SB-Mid-client-TEST-KEY-cafeflow"
MIDTRANS_MERCHANT_ID="G987654321"

# Pengaturan Toko
CAFE_TAX_PERCENT=10
CAFE_SERVICE_PERCENT=0
CAFE_CURRENCY="Rp"
```

### Langkah 5: Membuka Aplikasi di Browser
Anda dapat mengakses aplikasi melalui 2 cara:
1. **Melalui Apache XAMPP:**
   Buka browser ke alamat:
   ```
   http://localhost/penjualan_cafe/
   ```
   *(Atau `http://localhost/penjualan_cafe/public/`)*
2. **Melalui PHP Built-in Server (Alternatif Cepat):**
   ```powershell
   C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
   ```
   Lalu buka:
   ```
   http://127.0.0.1:8000/
   ```

---

## 💳 Panduan Pengujian Pembayaran

### 1. Pengujian Pembayaran Tunai (Cash)
1. Login sebagai Kasir (`kasir@cafeflow.com` / `kasir123`).
2. Di halaman **Kasir (POS)**:
   - Klik kartu menu (misal: *Kopi Susu Gula Aren* dan *Truffle French Fries*).
   - Masukkan catatan khusus bila diperlukan (misal: *Less sugar*).
   - Pilih jenis pesanan: **Dine-In** & nomor meja (misal: *Meja Indoor 01*).
3. Klik tombol hijau **TUNAI**.
4. Di modal pembayaran tunai, klik tombol cepat **100.000** atau ketik nominal uang yang diterima.
5. Perhatikan uang kembalian otomatis dihitung secara real-time.
6. Klik **Proses & Konfirmasi Bayar**.
7. Status pesanan seketika berubah menjadi **LUNAS (PAID)**, stok bahan baku berkurang otomatis, dan pratinjau struk thermal 58mm/80mm akan muncul siap dicetak.

### 2. Pengujian Pembayaran QRIS (Dengan Simulator Webhook)
1. Di halaman **Kasir (POS)**, masukkan menu ke keranjang.
2. Klik tombol biru **QRIS**.
3. Sistem akan membuat tagihan transaksi ke server dan memunculkan modal QRIS dinamis lengkap dengan:
   - Gambar QR code pembayaran unik.
   - Total tagihan terverifikasi backend.
   - Nomor pesanan & countdown waktu kedaluwarsa (15 menit).
   - Indikator status: *Menunggu Scan Pelanggan*.
4. **Pengujian Sandbox:** Di bagian bawah modal terdapat panel **Sandbox Developer Tool**.
5. Klik tombol **"Simulasi Bayar Sukses"**.
6. Sistem akan mengeksekusi payload simulasi resmi payment gateway ke webhook dengan signature SHA512 valid.
7. Frontend POS yang memantau status secara live akan mendeteksi verifikasi dalam 3 detik, mengubah status ke **PAID**, memunculkan centang sukses hijau, dan menyediakan tombol cetak struk!

---

## 📡 Daftar Endpoint API

| Method | Endpoint | Akses / Role | Deskripsi |
|---|---|---|---|
| `POST` | `/api/orders/create` | Kasir, Admin (CSRF) | Menyimpan pesanan dari keranjang POS dengan kalkulasi harga ulang di backend |
| `POST` | `/api/orders/{id}/status` | Kasir, Dapur, Admin | Memperbarui status pengerjaan pesanan (*processing*, *ready*, *completed*, *cancelled*) |
| `POST` | `/api/payments/cash` | Kasir, Admin (CSRF) | Memproses pembayaran uang tunai, validasi nominal, hitung kembalian, dan potong stok |
| `POST` | `/api/payments/qris` | Kasir, Admin (CSRF) | Mengirim tagihan ke payment gateway dan menghasilkan dynamic QRIS |
| `GET` | `/api/payments/status/{id}` | Kasir, Dapur, Admin | Endpoint polling real-time status pembayaran pesanan |
| `POST` | `/api/webhook/midtrans` | Public (Gateway) | Endpoint webhook resmi Midtrans dengan validasi signature SHA512 dan idempoten |
| `POST` | `/api/webhook/simulate` | Kasir, Admin (CSRF) | Endpoint simulator sandbox untuk memicu webhook buatan yang valid tanda tangannya |
| `GET` | `/api/kitchen/orders` | Dapur, Admin | Data live pesanan masuk untuk Kitchen Display System (KDS) |

---

## 🛡️ Mekanisme Keamanan & Konsistensi Data

1. **Prepared Statements & PDO**: Seluruh kueri database dieksekusi menggunakan prepared statement dengan binding parameter, mencegah serangan SQL Injection secara tuntas.
2. **CSRF Protection**: Form submit dan panggilan AJAX divalidasi menggunakan token CSRF acak yang disimpan di sesi pengguna.
3. **Validasi Harga di Backend**: Harga tiap item menu, diskon, persentase pajak restoran, dan total tagihan selalu dihitung ulang langsung dari database, mencegah manipulasi harga melalui inspect element browser.
4. **Transaksi Database (ACID)**: Penyimpanan pesanan, detail item pesanan, pembayaran, dan pemotongan mutasi inventaris berjalan di dalam `BEGIN TRANSACTION` dan `COMMIT`. Jika ada kesalahan data, database melakukan `ROLLBACK` penuh.
5. **Idempotency Guard pada Webhook**: Mencegah notifikasi berulang dari gateway memproses pembayaran atau mengurangi stok lebih dari 1 kali.
6. **Audit Activity Logs**: Mencatat setiap login, pesanan baru, pembayaran masuk, penyesuaian stok, dan aktivitas webhook lengkap dengan IP address dan User Agent.

---

## 🧪 Menjalankan Automated Test Suite
Aplikasi telah dilengkapi dengan *integration test runner* menyeluruh. Untuk memverifikasi seluruh modul:
```powershell
C:\xampp\php\php.exe database/test_runner.php
```
Hasil verifikasi mencakup 22 assertions:
- Verifikasi user hashing & RBAC.
- Pengecekan ketersediaan stok & resep.
- Kalkulasi akurat pesanan di backend.
- Pengurangan stok dan uji idempotensi.
- Pembuatan QRIS dinamis & verifikasi SHA512 Midtrans.
- Penolakan tanda tangan palsu / tampered.
- Logging audit webhook & sinkronisasi KDS.

---

*Dikembangkan dengan standar rekayasa perangkat lunak modern untuk CafeFlow POS &copy; 2026.*
