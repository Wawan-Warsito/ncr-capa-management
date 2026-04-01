# Quick Start (Local - Windows)

## Prasyarat
- PHP 8.2+
- Composer
- Node.js + npm

## Setup (SQLite - direkomendasikan)
```bash
cd C:\xampp\htdocs\ncr-capa-management
copy .env.example .env
php artisan key:generate
php artisan config:clear
php artisan migrate:fresh --seed
```

## Jalankan Aplikasi
```bash
# Terminal 1 (Backend)
php artisan serve --host=127.0.0.1 --port=8000

# Terminal 2 (Frontend)
npm install
npm run dev -- --host 127.0.0.1 --port 5173
```

## Akses & Login
- Frontend: http://127.0.0.1:5173/
- Email: `admin.ncr@tab-indonesia.co.id`
- Password: `password`

## Import ulang data NCR (CSV)
1. Buka menu NCR Management
2. Download Template (CSV)
3. Samakan header CSV Anda dengan template
4. Klik Import dan pilih file CSV

## Setup (MySQL/MariaDB - opsional)
Gunakan jika MySQL/MariaDB Anda stabil.

1) Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ncr_capa_db
DB_USERNAME=root
DB_PASSWORD=
```

2) Jalankan:
```bash
php artisan config:clear
php artisan migrate:fresh --seed
```
