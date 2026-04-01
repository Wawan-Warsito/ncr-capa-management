# Panduan Pengguna

## Pendahuluan
Selamat datang di Sistem Manajemen NCR-CAPA. Panduan ini akan membantu Anda menavigasi sistem, melaporkan Laporan Ketidaksesuaian (NCR), dan mengelola Tindakan Perbaikan dan Pencegahan (CAPA).

## Memulai

### Login
1. Buka halaman login (misalnya, `http://localhost:8000/login`).
2. Masukkan alamat email dan kata sandi Anda.
3. Klik "Sign In" (Masuk).

### Dashboard
Setelah login, Anda akan melihat Dashboard yang menampilkan:
- **Overview**: Statistik NCR dan CAPA yang terbuka/tertutup.
- **Recent Activity**: Pembaruan terbaru pada laporan yang Anda ikuti.
- **Pending Tasks**: Item yang memerlukan perhatian segera Anda.

## Mengelola NCR

### Melaporkan NCR Baru
1. Klik "New NCR" di menu navigasi.
2. Isi kolom yang diperlukan:
   - **NCR Number**: Dibuat otomatis atau input manual (jika diaktifkan).
   - **Project Name**: Pilih atau masukkan nama proyek.
   - **Department**: Identifikasi departemen Penemu (Finder) dan Penerima (Receiver).
   - **Defect Details**: Jelaskan masalah dengan jelas.
   - **Severity**: Pilih tingkat keparahan (Minor, Major, Critical).
   - **Attachments**: Unggah foto atau dokumen (opsional).
3. Klik "Submit". NCR akan dikirim ke Manajer Departemen Penemu untuk persetujuan.

### Meninjau & Menyetujui NCR (Manajer)
1. Pergi ke "My Approvals".
2. Pilih NCR dengan status "Pending Approval".
3. Tinjau detailnya.
4. Klik "Approve" untuk melanjutkan atau "Reject" untuk mengembalikan guna direvisi.

### Menetapkan Disposisi
1. Setelah disetujui oleh Manajer Penemu, Departemen Penerima akan diberitahu.
2. Manajer Penerima menetapkan disposisi (misalnya, Pengerjaan Ulang/Rework, Scrap).
3. Jika perlu, CAPA akan dimulai.

## Mengelola CAPA

### Membuat CAPA
1. Dari halaman detail NCR, klik "Create CAPA".
2. Tetapkan Person In Charge (PIC).
3. Tentukan Analisis Akar Masalah (Root Cause Analysis - RCA).
4. Buat Rencana Tindakan Perbaikan (Corrective Action Plan).
5. Tetapkan target tanggal penyelesaian.

### Memperbarui Kemajuan (PIC)
1. Pergi ke "My Tasks".
2. Buka CAPA yang ditugaskan.
3. Perbarui persentase kemajuan dan tambahkan komentar.
4. Tandai sebagai "Completed" (Selesai) setelah selesai.

### Verifikasi (Manajer QC)
1. Tinjau CAPA yang telah selesai.
2. Verifikasi efektivitas tindakan yang diambil.
3. Tutup CAPA jika memuaskan, atau buka kembali jika tidak efektif.

## Manajemen Akun
- **Profile**: Perbarui kata sandi dan informasi kontak Anda.
- **Logout**: Keluar dari sistem dengan aman.

## Dukungan
Untuk masalah teknis, silakan hubungi Departemen IT atau kirim tiket melalui portal Helpdesk.
