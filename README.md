# Toko Kila - E-Commerce Hijab Premium

Toko Kila adalah aplikasi e-commerce sederhana berbasis web yang dirancang khusus untuk mempermudah penjualan produk hijab premium secara online. Aplikasi ini menggunakan integrasi WhatsApp Checkout instan, memungkinkan pembeli memesan produk secara langsung ke nomor WhatsApp penjual dengan format pesan yang sudah tersusun rapi.

## 🚀 Fitur Utama

- **Keranjang Belanja Dinamis**: Pengguna dapat dengan mudah menambahkan, mengurangi, atau menghapus produk yang ingin dibeli sebelum melakukan checkout.
- **WhatsApp Checkout Terintegrasi**: Menggunakan pengalihan server-side (`Location header`) yang 100% aman dari blokir popup browser modern. Data pemesanan langsung tersusun rapi di WhatsApp.
- **Dua Metode Pembayaran**: 
  - **QRIS**: Scan kode QR otomatis langsung di halaman checkout.
  - **Transfer Bank BCA**: Menyediakan informasi nomor rekening dan nama pemilik secara langsung.
- **Dynamic Toggle Info Pembayaran**: Panel instruksi pembayaran QRIS / Bank BCA otomatis bergantian tampil secara dinamis sesuai pilihan pembeli.
- **Panel Admin (Dashboard Admin)**:
  - **Manajemen Produk**: Tambah produk (dengan upload gambar), Edit produk, dan Hapus produk.
  - **Riwayat Pesanan**: Melihat seluruh daftar pelanggan dan transaksi yang masuk.
- **Sistem Keamanan Admin**: Semua halaman admin diproteksi dengan autentikasi berbasis session sehingga aman dari akses tidak resmi.

---

## 🛠️ Teknologi yang Digunakan

- **Backend / Logika**: PHP (Native)
- **Database**: MySQL / MariaDB
- **Desain & Interface**: HTML5, Vanilla CSS3 (Custom responsive styling), Font Awesome Icons
- **Interaktivitas**: JavaScript (Vanilla ES6)

---

## 📋 Detail Kredensial Admin Default

Untuk mengakses dashboard admin, gunakan kredensial berikut:
- **URL Akses**: `http://localhost/tokokila/admin/login.php` (sesuaikan dengan subfolder server lokal Anda)
- **Username**: `admin`
- **Password**: `admin123`

---

## 💻 Panduan Instalasi Lokal

### 1. Prasyarat
Pastikan Anda sudah mengunduh dan menjalankan paket server lokal seperti **XAMPP** (Apache dan MySQL).

### 2. Impor Database
1. Buka **phpMyAdmin** di browser Anda (`http://localhost/phpmyadmin`).
2. Buat database baru dengan nama `sakila_store`.
3. Klik tab **Import**, pilih berkas SQL database yang terletak di `database/sakila_store.sql` dalam repositori ini, lalu klik **Go** / **Kirim**.

### 3. Konfigurasi Koneksi Database
Jika Anda menggunakan port MySQL custom atau password database yang berbeda, Anda dapat mengubah pengaturannya pada berkas `koneksi.php`:
```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sakila_store";
```

### 4. Menjalankan Aplikasi
Pindahkan seluruh folder project `tokokila` ke dalam direktori root server lokal Anda:
- **XAMPP**: `C:\xampp\htdocs\`
- Buka browser dan ketik alamat: `http://localhost/tokokila/index.php`

---

## 📁 Struktur Folder Proyek

```text
tokokila/
├── admin/                  # Seluruh berkas pengelolaan dashboard admin
│   ├── upload/             # Folder untuk menyimpan file gambar produk yang diunggah
│   ├── dashboard.php       # Halaman utama admin list produk
│   ├── edit_produk.php     # Halaman edit data produk
│   ├── hapus_produk.php    # Script hapus produk
│   ├── login.php           # Form login admin
│   ├── logout.php          # Script logout admin
│   ├── riwayat_pesanan.php # Halaman list order/transaksi masuk
│   └── update_status.php   # Script update status pembayaran
├── database/               # Berkas SQL backup database
│   └── sakila_store.sql    # Dump SQL schema & data awal
├── index.php               # Halaman utama katalog toko & keranjang belanja
├── koneksi.php             # Berkas koneksi PHP ke MySQL
├── proses_checkout.php     # Proses checkout, input order DB, & redirect ke WhatsApp
├── README.md               # Dokumentasi proyek
└── style.css               # Desain layout dan styling kustom website
```
