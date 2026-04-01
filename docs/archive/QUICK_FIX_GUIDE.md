# 🔧 Quick Fix Guide - Node.js & NPM Issues

## ❌ Error yang Anda Alami

### Error 1: "Could not read package.json"
```
npm error path C:\Users\p\package.json
npm error errno -4058
```

**Penyebab:** Anda menjalankan `npm run dev` di folder yang salah (`C:\Users\p\`)

**Solusi:** Harus masuk ke folder project dulu!

---

### Error 2: "npm is not recognized"
```
npm : The term 'npm' is not recognized
```

**Penyebab:** Node.js belum terinstall ATAU terminal belum direstart setelah instalasi

**Solusi:** Install Node.js atau restart terminal

---

## ✅ SOLUSI LENGKAP

### Langkah 1: Cek Apakah Node.js Sudah Terinstall

Buka **terminal/command prompt BARU** (jangan yang lama), lalu ketik:

```bash
node --version
```

**Jika muncul versi (contoh: v20.11.0):**
- ✅ Node.js sudah terinstall
- Lanjut ke Langkah 2

**Jika muncul error "not recognized":**
- ❌ Node.js belum terinstall atau belum direstart
- Lakukan ini:
  1. **Tutup SEMUA terminal/command prompt**
  2. **Buka terminal BARU**
  3. Coba lagi `node --version`
  4. Jika masih error, install Node.js (lihat NODEJS_INSTALLATION_GUIDE.md)

---

### Langkah 2: Masuk ke Folder Project

**PENTING:** Anda HARUS masuk ke folder project dulu!

```bash
cd C:\xampp\htdocs\ncr-capa-management
```

Verifikasi Anda sudah di folder yang benar:
```bash
dir
```

Anda harus melihat file-file ini:
- package.json ✅
- composer.json ✅
- artisan ✅
- app/ ✅
- resources/ ✅

---

### Langkah 3: Install Dependencies (Jika Belum)

```bash
npm install
```

Tunggu sampai selesai (2-5 menit). Anda akan melihat:
```
added 123 packages in 2m
```

---

### Langkah 4: Jalankan Development Server

```bash
npm run dev
```

**Expected Output:**
```
VITE v5.x.x  ready in xxx ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
➜  press h + enter to show help
```

---

## 🎯 COMMAND LENGKAP (Copy-Paste)

Buka **terminal baru**, lalu copy-paste ini:

```bash
cd C:\xampp\htdocs\ncr-capa-management
npm install
npm run dev
```

---

## 🐛 TROUBLESHOOTING

### Problem: "npm is not recognized" (setelah install Node.js)

**Solusi:**
1. **Tutup SEMUA terminal** (PENTING!)
2. **Buka terminal BARU**
3. Coba lagi

Jika masih error:
4. **Restart komputer**
5. Buka terminal baru
6. Coba lagi

---

### Problem: "Could not read package.json"

**Solusi:**
Pastikan Anda di folder yang benar:

```bash
# Cek lokasi Anda sekarang
pwd

# Harus menunjukkan:
# C:\xampp\htdocs\ncr-capa-management

# Jika tidak, masuk ke folder project:
cd C:\xampp\htdocs\ncr-capa-management
```

---

### Problem: "npm install" gagal

**Solusi:**
1. Hapus folder `node_modules` (jika ada)
2. Hapus file `package-lock.json` (jika ada)
3. Jalankan ulang:
   ```bash
   npm install
   ```

---

### Problem: Port 5173 sudah digunakan

**Solusi:**
```bash
npm run dev -- --port 5174
```

---

## ✅ CHECKLIST SEBELUM MENJALANKAN

Pastikan semua ini sudah dilakukan:

- [ ] Node.js sudah terinstall (`node --version` berhasil)
- [ ] Terminal sudah direstart setelah install Node.js
- [ ] Sudah masuk ke folder project (`cd C:\xampp\htdocs\ncr-capa-management`)
- [ ] File `package.json` ada di folder tersebut
- [ ] Sudah jalankan `npm install`
- [ ] Tidak ada error saat `npm install`

Jika semua ✅, baru jalankan:
```bash
npm run dev
```

---

## 📞 JIKA MASIH ERROR

Screenshot error message dan kirim ke saya dengan info:
1. Output dari `node --version`
2. Output dari `npm --version`
3. Output dari `pwd` (lokasi folder Anda)
4. Screenshot error lengkap

---

## 🚀 SETELAH BERHASIL

Jika `npm run dev` berhasil, Anda akan melihat:
```
VITE v5.x.x  ready in xxx ms
➜  Local:   http://localhost:5173/
```

**Jangan tutup terminal ini!** Biarkan tetap running.

Buka browser dan akses: http://localhost:5173/

Anda akan melihat aplikasi React (masih kosong karena belum ada frontend code).

---

**Good luck! 🎉**
