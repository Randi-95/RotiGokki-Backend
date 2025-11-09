<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


# RotiGokki - Backend (Laravel 12)

Ini adalah repository resmi untuk layanan backend aplikasi **RotiGokki**. Proyek ini dibangun menggunakan framework **Laravel 12** dan berfungsi sebagai API untuk mengelola produk, pesanan, otentikasi, dan operasi lainnya.

---

## Prasyarat

Sebelum Anda memulai, pastikan perangkat lunak berikut telah terinstal di sistem Anda:

* **PHP** (Versi >= 8.2)
* **Composer** (Manajer paket PHP)
* **Database** (MySQL)
* **Git** (Sistem kontrol versi)

---

## 🚀 Langkah-Langkah Instalasi

Berikut adalah panduan langkah demi langkah untuk mengkloning dan menjalankan proyek ini di lingkungan lokal Anda.

### 1. Clone Repository

Buka terminal Anda dan jalankan perintah berikut untuk mengunduh file proyek:

```bash
git clone https://github.com/Randi-95/RotiGokki-Backend
cd RotiGokki-Backend
```

### 2.Install Dependensi Composer

Install semua paket PHP yang dibutuhkan oleh proyek:

```bash
composer install
```

### 3. Buat File Environment (.env)

Salin file .env.example sebagai dasar untuk file konfigurasi Anda:

```bash
cp .env.example .env
```
### 4. Generate Application Key

Setiap aplikasi Laravel membutuhkan kunci unik. Hasilkan kunci tersebut dengan perintah:

```bash
php artisan key:generate
```

### 5.  Konfigurasi Database

Buka file .env yang baru Anda buat dan atur koneksi database Anda. Pastikan Anda sudah membuat database kosong (misalnya, bernama db_rotigokki) sebelum lanjut ke langkah berikutnya.

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE= rotigokkibackend // <-- Ganti dengan nama database Anda
DB_USERNAME=root            // <-- Ganti dengan username database Anda
DB_PASSWORD=               // <-- Ganti dengan password database Anda
```

### 6. Import Database 

Proyek ini menggunakan database dump (file .sql). Anda tidak perlu menjalankan php artisan migrate.

### 7.  Buat Storage Link

Setelah database terisi, jalankan perintah ini. Ini akan membuat symbolic link agar file yang diunggah (seperti gambar produk) dapat diakses secara publik.

```bash
php artisan storage:link
```

### 8.  Jalankan server lokal  

Setelah semua langkah instalasi selesai, Anda dapat menjalankan server pengembangan lokal:

```bash
php artisan serve
```
