# Contributing Guide

## Branching
- `main`: stabil, siap rilis/demo.
- `dev`: pengembangan harian.
- Fitur/bug: `feat/<nama-fitur>` atau `fix/<deskripsi-bug>`.

## Commit
- Pesan commit singkat dan jelas, format: `feat:`, `fix:`, `docs:`, `chore:`, `test:`.
- Satu commit untuk satu perubahan logis.

## Code Style
- PHP: PSR-12, gunakan type hints dan naming konsisten.
- JS/React: gunakan ES Modules, functional components, hooks.
- Hindari log sensitif, jangan commit `.env` dan data pribadi.

## Testing
- Jalankan sebelum push:
  - `php artisan test`
  - `npm run build` (pastikan tidak ada error)

## Environment (Local)
- Default DB: SQLite (`DB_CONNECTION=sqlite`).
- MySQL/MariaDB opsional; pastikan service stabil sebelum mengubah `.env`.

## Build & Preview
- Development:
  - Backend: `php artisan serve --host 127.0.0.1 --port 8000`
  - Frontend: `npm run dev -- --host 127.0.0.1 --port 5173`
- Production-like:
  - `npm run build`
  - `npm run preview` (serving asset build di port 4173; backend tetap via `php artisan serve`).

## Pull Request Checklist
- [ ] Sudah lulus `php artisan test`
- [ ] Tidak mengubah `.env` dan file private
- [ ] Dokumentasi diperbarui bila ada perubahan fitur
- [ ] Perubahan mengikuti arsitektur dan pola yang ada

## Security
- Jangan commit secret key atau kredensial.
- Validasi input di server; sanitasi output di client.

## Dokumentasi
- Tambahkan/ubah dokumen di `docs/` (developer → `docs/dev`, panduan user → `docs/handbook`).

