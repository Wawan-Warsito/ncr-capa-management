# 📦 Node.js Installation Guide - NCR CAPA Management System

## ⚠️ PENTING: Node.js Diperlukan untuk Frontend Development

Untuk melanjutkan Phase 3 (Frontend Development dengan React), Anda **WAJIB** install Node.js terlebih dahulu.

---

## 🚀 LANGKAH INSTALASI NODE.JS (Windows)

### Step 1: Download Node.js

1. **Buka browser** dan kunjungi: https://nodejs.org/
2. Anda akan melihat 2 pilihan:
   - **LTS (Long Term Support)** - RECOMMENDED ✅
   - Current (Latest Features)
3. **Klik tombol hijau "LTS"** untuk download
   - Contoh: Node.js 20.11.0 LTS atau 18.19.0 LTS
   - File yang didownload: `node-v20.11.0-x64.msi` (sekitar 30-40 MB)

### Step 2: Install Node.js

1. **Buka file installer** yang sudah didownload
2. **Klik "Next"** pada welcome screen
3. **Centang "I accept the terms"** → Klik "Next"
4. **Pilih lokasi instalasi** (default: `C:\Program Files\nodejs\`) → Klik "Next"
5. **Custom Setup** → Biarkan default (semua tercentang) → Klik "Next"
6. **Tools for Native Modules** → Biarkan default → Klik "Next"
7. **Klik "Install"** → Tunggu proses instalasi (2-3 menit)
8. **Klik "Finish"**

### Step 3: Restart Terminal/Command Prompt

**PENTING:** Setelah instalasi, Anda HARUS restart terminal!

1. **Tutup semua terminal/command prompt** yang sedang terbuka
2. **Buka terminal baru** (PowerShell atau Command Prompt)

### Step 4: Verifikasi Instalasi

Buka terminal baru dan jalankan:

```bash
node --version
```

**Expected Output:**
```
v20.11.0
```
(atau versi lain yang Anda install)

Lalu cek npm:
```bash
npm --version
```

**Expected Output:**
```
10.2.4
```
(atau versi lain)

### ✅ Jika kedua command di atas berhasil, Node.js sudah terinstall dengan benar!

---

## 🎯 SETELAH NODE.JS TERINSTALL

### Langkah Selanjutnya:

1. **Buka terminal baru** (PowerShell atau Command Prompt)

2. **Masuk ke folder project:**
   ```bash
   cd C:\xampp\htdocs\ncr-capa-management
   ```

3. **Install dependencies yang sudah ada:**
   ```bash
   npm install
   ```
   
   Ini akan install:
   - Vite (build tool)
   - Tailwind CSS (styling)
   - Axios (HTTP client)
   - Dan dependencies lainnya yang sudah dikonfigurasi

4. **Hubungi saya kembali** dengan mengatakan:
   "Node.js sudah terinstall, lanjutkan frontend development"

5. **Saya akan melanjutkan** dengan:
   - Install React dependencies
   - Membuat Login page
   - Membuat Dashboard
   - Membuat NCR/CAPA forms
   - Setup routing
   - Testing frontend

---

## 🐛 TROUBLESHOOTING

### Problem 1: "node is not recognized"
**Solusi:**
1. Restart terminal (WAJIB!)
2. Jika masih error, restart komputer
3. Cek PATH environment variable:
   - Buka "Environment Variables"
   - Cek apakah `C:\Program Files\nodejs\` ada di PATH
   - Jika tidak ada, tambahkan manual

### Problem 2: "npm is not recognized"
**Solusi:**
- npm otomatis terinstall dengan Node.js
- Restart terminal
- Jika masih error, reinstall Node.js

### Problem 3: Instalasi gagal
**Solusi:**
1. Uninstall Node.js yang ada
2. Download ulang installer
3. Run installer as Administrator (klik kanan → Run as Administrator)

### Problem 4: Versi Node.js terlalu lama
**Solusi:**
- Uninstall versi lama
- Install versi LTS terbaru dari nodejs.org

---

## 📊 VERSI YANG DIREKOMENDASIKAN

| Software | Minimum Version | Recommended |
|----------|----------------|-------------|
| Node.js | v16.x | v20.x LTS ✅ |
| npm | v8.x | v10.x ✅ |

---

## ⏱️ ESTIMASI WAKTU

- Download Node.js: 2-5 menit (tergantung internet)
- Instalasi: 2-3 menit
- Verifikasi: 1 menit
- **Total: 5-10 menit**

---

## 🎯 CHECKLIST INSTALASI

Pastikan semua langkah ini sudah dilakukan:

- [ ] Download Node.js LTS dari nodejs.org
- [ ] Install Node.js (ikuti wizard)
- [ ] Restart terminal/command prompt
- [ ] Verifikasi: `node --version` berhasil
- [ ] Verifikasi: `npm --version` berhasil
- [ ] Masuk ke folder project: `cd C:\xampp\htdocs\ncr-capa-management`
- [ ] Run: `npm install`
- [ ] Hubungi developer untuk lanjut frontend

---

## 📞 BANTUAN

Jika mengalami kesulitan:
1. Screenshot error message
2. Cek versi Windows Anda
3. Pastikan Anda punya akses Administrator
4. Coba install ulang

---

## 🚀 SETELAH SELESAI

Setelah Node.js terinstall dan `npm install` berhasil, saya akan melanjutkan dengan:

### Phase 3: Frontend Development
1. ✅ Install React dependencies
2. ✅ Setup React Router
3. ✅ Create Login page
4. ✅ Create Dashboard (3 views)
5. ✅ Create NCR Management
6. ✅ Create CAPA Management
7. ✅ Setup API integration
8. ✅ Testing & debugging

**Estimasi waktu Phase 3:** 2-3 jam

---

## 💡 TIPS

- Gunakan Node.js LTS (bukan Current) untuk stabilitas
- Restart terminal setelah instalasi (PENTING!)
- Jangan install di folder dengan spasi atau karakter khusus
- Pastikan antivirus tidak block instalasi

---

**Good luck! Setelah Node.js terinstall, kita lanjut ke frontend development! 🚀**
