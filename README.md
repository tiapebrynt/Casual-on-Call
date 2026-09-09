# CoC (Casual on Call) &mdash; Marketplace Tenaga Kerja Harian (Casual Worker)

Platform marketplace penghubung pekerja harian (*casual worker*) dengan perusahaan/bisnis (Hospitality, Event Organizer, Retail, Logistik). Dilengkapi dengan sistem absensi, sistem dompet/rekening penampung (Escrow), review & rating dua arah, serta laporan otomatis.

---

### 🔑 Akun Demo Siap Pakai:
- **Pekerja (Worker)**: `worker@casualhub.id`
- **Perusahaan (Company)**: `company@casualhub.id`
- **Administrator (Admin)**: `admin@casualhub.id`
- **Password untuk semua akun**: `Password123!`

---

## ⚙️ Panduan Setup untuk Kolaborator (Clone / Pull dari GitHub)

Bagi teman atau kolaborator yang baru saja melakukan `git clone` atau `git pull`, lakukan langkah-langkah berikut di terminal:

```bash
# 1. Install dependensi backend (PHP/Laravel)
composer install

# 2. Install dependensi frontend (Tailwind/Vite)
npm install

# 3. Salin environment file (jika clone baru)
cp .env.example .env

# 4. Generate App Encryption Key
php artisan key:generate

# 5. Hubungkan storage lokal (untuk upload & download CV)
php artisan storage:link

# 6. Setup Database & Jalankan Seeder
# Pastikan konfigurasi database di .env sudah sesuai (bisa SQLite atau MySQL)
php artisan migrate:fresh --seed

# 7. Compile asset frontend
npm run build

# 8. Jalankan local server
php artisan serve
```

---

## 💳 Penjelasan Sistem Pembayaran & Dana Mengendap (Escrow)

1. **Apakah menggunakan API Payment Gateway?**
   - Saat ini sistem menggunakan **Native Ledger / Simulated Gateway** berarsitektur modular yang mendukung metode: **BCA VA, Mandiri VA, BRI VA, BNI VA, dan QRIS**.
   - Sistem ini siap dihubungkan langsung ke vendor API pihak ketiga (seperti **Midtrans** atau **Xendit**) saat akun merchant / API Key sudah tersedia.

2. **Bagaimana Konsep "Dana Mengendap" (Escrow)?**
   - **Saat Invoice Dibuat**: Pekerjaan yang diselesaikan akan menghasilkan invoice, dan upah worker sementara tercatat sebagai `pending_balance` (saldo tertunda).
   - **Pengendapan Dana**: Uang "mengendap" di sistem CoC sebagai jaminan keamanan dua arah (perusahaan terlindungi dari pekerja yang tidak hadir/absen, dan pekerja terjamin hak upahnya pasti cair).
   - **Rilis Dana**: Begitu perusahaan melakukan verifikasi dan menekan tombol konfirmasi bayar, dana dari `pending_balance` langsung dikonversi menjadi `balance` aktif di dompet worker.
   - **Pencairan (Withdraw)**: Worker dapat mencairkan saldo aktifnya kapan saja ke rekening bank tujuan.


<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
