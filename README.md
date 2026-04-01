# NCR–CAPA Management System

![CI](https://github.com/Wawan-Warsito/ncr-capa-management/actions/workflows/ci.yml/badge.svg) ![Frontend Build](https://github.com/Wawan-Warsito/ncr-capa-management/actions/workflows/frontend.yml/badge.svg)

Sistem manajemen Non-Conformance Report (NCR) dan Corrective and Preventive Action (CAPA) berbasis web untuk studi kasus industri fabrikasi stainless steel.

## Deskripsi
Sistem ini dikembangkan untuk mendigitalisasi proses NCR–CAPA yang sebelumnya dilakukan secara manual (mis. spreadsheet). Fokus utama sistem adalah workflow lintas departemen (Finder–Receiver), approval berjenjang, assignment PIC, keterlacakan (audit trail), dashboard, dan pelaporan.

## Fitur
- NCR: create, edit, submit, approve/reject, assign PIC, close
- CAPA: create from NCR, RCA (5 Why/Fishbone), progress, verification, close
- Dashboard: company/department/personal (role-based)
- Notifikasi in-app dan activity log (audit trail)
- Import NCR (CSV/XLS/XLSX) + template CSV
- Attachments (NCR/CAPA) + QR code untuk print NCR

## Tech Stack
- Backend: Laravel 12 (PHP 8.2+)
- Frontend: React 19 + Vite 7
- UI: Tailwind CSS 4
- Charts: Recharts
- Icons: lucide-react
- Auth: Laravel Sanctum
- Data: SQLite (local) atau MySQL/MariaDB (opsional)

## Instalasi (Windows)
### Prasyarat
- PHP 8.2+
- Composer
- Node.js + npm

### Catatan Repository Publik
- Jangan commit `.env`, `vendor/`, `node_modules/`, log, dan data legacy.
- Dokumen pedoman kampus dan naskah proposal/skripsi disarankan disimpan di luar repository (private).
- Folder `local/` digunakan untuk menyimpan file private (pedoman, draft output), dan di-ignore dari Git.
- Index dokumentasi: `docs/INDEX.md`.

### Setup (SQLite, direkomendasikan untuk local)
```bash
cd C:\xampp\htdocs\ncr-capa-management
copy .env.example .env
php artisan key:generate
php artisan config:clear
php artisan migrate:fresh --seed
```

Jalankan aplikasi:
```bash
# Terminal 1
php artisan serve --host=127.0.0.1 --port=8000

# Terminal 2
npm install
npm run dev -- --host 127.0.0.1 --port 5173
```

Akses:
- Frontend: http://127.0.0.1:5173/
- API: http://127.0.0.1:8000/

### Setup (MySQL/MariaDB, opsional)
Gunakan ini jika MySQL Anda stabil:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ncr_capa_db
DB_USERNAME=root
DB_PASSWORD=
```

## Import ulang 216 NCR (CSV)
1. Buka menu NCR Management
2. Download Template (CSV)
3. Sesuaikan header CSV Anda dengan template
4. Klik Import dan pilih file CSV

## Default Login (Seeder)
Password default untuk semua user seeded: `password`
- Admin: `admin.ncr@tab-indonesia.co.id`
- QC Manager: `qc.manager@tab-indonesia.co.id`
- Dept Manager (contoh): `prod.manager@tab-indonesia.co.id`, `proc.manager@tab-indonesia.co.id`, `design.manager@tab-indonesia.co.id`
- User (contoh): `qc.inspector1@tab-indonesia.co.id`, `purch.specialist@tab-indonesia.co.id`

## Testing
```bash
php artisan test
npm run build
```

## Preview (Production-like)
```bash
npm run build
# Jalankan backend pada port 8000
php artisan serve --host=127.0.0.1 --port=8000
# Serve asset build pada port 4173
npm run preview
```

## Dokumentasi
- Index: `docs/INDEX.md`
- SOP Workflow: `docs/SOP_WORKFLOW_NCR_CAPA.md`
- Diagram Workflow (Mermaid): `docs/SOP_WORKFLOW_NCR_CAPA.mmd`
