# Laravel JSON API - Iyen

Backend REST API siap produksi untuk SPA Vue.js dengan ULID, JWT Auth, dan Spatie Permission.

## 🚀 Fitur Utama

- ✅ **ULID Primary Key** - Semua model menggunakan ULID (Universally Unique Lexicographically Sortable Identifier)
- ✅ **JWT Authentication** - Login, Logout, Refresh Token, dan Get Profile
- ✅ **Role & Permission** - Menggunakan Spatie Permission dengan dukungan ULID
- ✅ **Profile Management** - Update profile, password, dan avatar
- ✅ **Full CRUD** - User, Role, dan Permission management
- ✅ **API Resources** - Format response konsisten menggunakan JsonResource
- ✅ **Testing** - 31 test cases dengan 222 assertions
- ✅ **Redis Ready** - Konfigurasi Redis untuk cache, queue, dan session

## 📋 Daftar Isi

- [Persyaratan](#-persyaratan)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Struktur Database](#-struktur-database)
- [Daftar API](#-daftar-api)
- [Testing](#-testing)
- [Akun Default](#-akun-default)
- [Lisensi](#-lisensi)

## 📦 Persyaratan

- PHP >= 8.2 (Direkomendasikan PHP 8.3)
- Composer 2.x
- MySQL 5.7+ atau MariaDB 10.3+
- Redis (Opsional, untuk production)
- ekstensi PHP: pdo_mysql, mysqli, openssl, mbstring

## 🔧 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/iyendoang/laravel-json-iyen-api.git
cd laravel-json-iyen-api