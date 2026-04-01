# 🚀 Panduan Menjalankan Aplikasi NCR-CAPA di VS Code

Berikut adalah langkah-langkah untuk menjalankan aplikasi NCR-CAPA Management System menggunakan Visual Studio Code (VS Code).

## 📋 Prasyarat (Pastikan Sudah Terinstall)
1.  **XAMPP** (Pastikan Apache & MySQL aktif)
2.  **Node.js** (Versi 18 atau terbaru)
3.  **Composer**
4.  **VS Code**

---

## 🛠️ Langkah 1: Persiapan Database
1.  Buka **XAMPP Control Panel** dan klik **Start** pada **Apache** dan **MySQL**.
2.  Buka browser dan akses [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3.  Buat database baru dengan nama: `ncr_capa_db`.

---

## ⚙️ Langkah 2: Konfigurasi Environment (.env)
1.  Buka folder proyek `ncr-capa-management` di VS Code.
2.  Duplikat file `.env.example` dan ubah namanya menjadi `.env`.
3.  Pastikan konfigurasi database di file `.env` sesuai:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=ncr_capa_db
    DB_USERNAME=root
    DB_PASSWORD=
    ```

---

## 📦 Langkah 3: Install Dependensi & Setup Database
Buka **Terminal** di VS Code (`Ctrl + ` `) dan jalankan perintah berikut secara berurutan:

1.  **Install Dependensi PHP (Laravel):**
    ```bash
    composer install
    ```
2.  **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```
3.  **Migrasi & Seeding Database (Membuat tabel & data awal):**
    ```bash
    php artisan migrate:fresh --seed
    ```
4.  **Install Dependensi JavaScript (React/Frontend):**
    ```bash
    npm install
    ```

---

## ▶️ Langkah 4: Menjalankan Aplikasi
Anda perlu menjalankan **dua terminal** secara bersamaan agar aplikasi berjalan penuh (Backend & Frontend).

### Terminal 1: Menjalankan Backend (Laravel)
```bash
php artisan serve
```
*Output: Server running on [http://127.0.0.1:8000](http://127.0.0.1:8000)*

### Terminal 2: Menjalankan Frontend (Vite)
Buka tab terminal baru (`Ctrl + Shift + ` `) dan jalankan:
```bash
npm run dev
```
*Output: Local: [http://localhost:5173/](http://localhost:5173/)*

---

## 🌐 Langkah 5: Akses Aplikasi
1.  Buka browser (Chrome/Edge).
2.  Ketik alamat: **[http://localhost:8000](http://localhost:8000)** (atau link yang muncul di Terminal 1).
3.  Anda akan diarahkan ke halaman Login.

### 🔑 Akun Login Default
Gunakan salah satu akun berikut untuk masuk:

*   **Administrator (Super Admin):**
    *   Email: `admin.ncr@tab-indonesia.co.id`
    *   Password: `password`

*   **QC Manager:**
    *   Email: `qc.manager@tab-indonesia.co.id`
    *   Password: `password`

*   **Production Manager:**
    *   Email: `prod.manager@tab-indonesia.co.id`
    *   Password: `password`

---

## ⚠️ Troubleshooting Umum
*   **Error 500 / Blank Screen:** Coba jalankan `php artisan optimize:clear`.
*   **Database Error:** Pastikan XAMPP MySQL sudah Start dan nama database di `.env` sudah benar.
*   **Vite Error:** Pastikan `npm install` sudah selesai tanpa error.
