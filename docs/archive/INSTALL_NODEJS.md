# 📦 Cara Install Node.js & NPM

## ❌ Error yang Anda Alami

```
'npm' is not recognized as an internal or external command,
operable program or batch file.
```

**Penyebab**: Node.js belum terinstall di komputer Anda.

---

## ✅ Solusi: Install Node.js

### Option 1: Download & Install (Recommended)

#### Step 1: Download Node.js
1. Buka browser
2. Kunjungi: **https://nodejs.org/**
3. Download versi **LTS (Long Term Support)** - Recommended
   - Contoh: Node.js 20.x LTS
4. Pilih installer untuk Windows (`.msi` file)

#### Step 2: Install Node.js
1. Jalankan file installer yang sudah didownload
2. Klik **Next** pada welcome screen
3. **Accept** license agreement
4. Pilih lokasi instalasi (default: `C:\Program Files\nodejs\`)
5. **Centang semua options**, terutama:
   - ✅ Node.js runtime
   - ✅ npm package manager
   - ✅ Add to PATH
6. Klik **Next** → **Install**
7. Tunggu hingga selesai (2-3 menit)
8. Klik **Finish**

#### Step 3: Verify Installation
Buka **Command Prompt baru** (penting: buka yang baru!) dan jalankan:

```bash
node -v
# Output: v20.x.x (atau versi yang Anda install)

npm -v
# Output: 10.x.x (atau versi npm yang terinstall)
```

Jika kedua command menampilkan versi, berarti **instalasi berhasil!** ✅

---

### Option 2: Install via Chocolatey (Advanced)

Jika Anda sudah punya Chocolatey package manager:

```bash
# Buka PowerShell sebagai Administrator
choco install nodejs-lts

# Verify
node -v
npm -v
```

---

## 🔄 Setelah Node.js Terinstall

### 1. Restart Command Prompt
**PENTING**: Tutup semua Command Prompt/PowerShell yang terbuka, lalu buka yang baru.

### 2. Verify PATH
```bash
# Check apakah Node.js sudah di PATH
where node
# Output: C:\Program Files\nodejs\node.exe

where npm
# Output: C:\Program Files\nodejs\npm.cmd
```

### 3. Update NPM (Optional tapi Recommended)
```bash
npm install -g npm@latest
```

### 4. Lanjutkan Instalasi Project
```bash
cd C:\xampp\htdocs\ncr-capa-management
npm install
```

---

## 🎯 Troubleshooting

### Issue 1: "npm not found" setelah install
**Solusi**:
1. Restart komputer
2. Atau tambahkan manual ke PATH:
   - Klik kanan **This PC** → **Properties**
   - **Advanced system settings**
   - **Environment Variables**
   - Edit **Path** di System variables
   - Tambahkan: `C:\Program Files\nodejs\`
   - Klik **OK**
   - Restart Command Prompt

### Issue 2: Permission Error saat npm install
**Solusi**:
```bash
# Jalankan Command Prompt sebagai Administrator
# Atau gunakan:
npm install --legacy-peer-deps
```

### Issue 3: Slow Download
**Solusi**:
```bash
# Gunakan mirror registry
npm config set registry https://registry.npmmirror.com
npm install
```

---

## 📋 Checklist Instalasi

- [ ] Download Node.js LTS dari nodejs.org
- [ ] Install Node.js (centang "Add to PATH")
- [ ] Restart Command Prompt
- [ ] Verify: `node -v` menampilkan versi
- [ ] Verify: `npm -v` menampilkan versi
- [ ] Update npm: `npm install -g npm@latest`
- [ ] Navigate ke project: `cd C:\xampp\htdocs\ncr-capa-management`
- [ ] Install dependencies: `npm install`
- [ ] Verify: folder `node_modules` terbuat

---

## 🚀 Setelah NPM Terinstall

Lanjutkan dengan instalasi project:

```bash
# 1. Navigate ke project
cd C:\xampp\htdocs\ncr-capa-management

# 2. Install NPM dependencies
npm install
# Tunggu 2-5 menit (download ~200MB dependencies)

# 3. Install Composer dependencies (jika belum)
composer install

# 4. Setup environment
copy .env.example .env
php artisan key:generate
php artisan storage:link

# 5. Run development server
# Terminal 1:
php artisan serve

# Terminal 2 (buka terminal baru):
npm run dev

# 6. Access application
# Browser: http://localhost:8000
```

---

## 💡 Tips

1. **Selalu gunakan Node.js LTS** (bukan Current) untuk stability
2. **Restart Command Prompt** setelah install Node.js
3. **Update npm** ke versi terbaru: `npm install -g npm@latest`
4. **Gunakan nvm** (Node Version Manager) jika perlu multiple Node versions
5. **Check PATH** jika command tidak ditemukan

---

## 📊 Versi yang Direkomendasikan

- **Node.js**: v20.x LTS atau v18.x LTS
- **NPM**: v10.x atau lebih tinggi
- **Minimum**: Node.js v16.x, NPM v8.x

---

## 🔗 Links

- **Node.js Official**: https://nodejs.org/
- **NPM Documentation**: https://docs.npmjs.com/
- **Node Version Manager (nvm)**: https://github.com/nvm-sh/nvm

---

## ✅ Verification Commands

Setelah instalasi, jalankan semua command ini untuk verify:

```bash
# Check Node.js version
node -v

# Check NPM version
npm -v

# Check NPM global packages location
npm config get prefix

# Check NPM registry
npm config get registry

# List installed global packages
npm list -g --depth=0

# Check if npm can install packages
npm install -g npm-check
```

Jika semua command berjalan tanpa error, **Node.js & NPM sudah siap!** ✅

---

**Setelah Node.js terinstall, kembali ke `QUICK_START.md` untuk melanjutkan instalasi project.**
