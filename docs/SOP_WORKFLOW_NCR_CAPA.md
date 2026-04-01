# SOP Workflow NCR–CAPA (Ringkas)

Dokumen ini menjelaskan alur kerja resmi NCR–CAPA berdasarkan implementasi aplikasi saat ini.

## Peran (Role)
- User (Finder): membuat NCR
- Finder Department Manager: approval awal NCR
- QC Manager: QC Registration + verifikasi efektivitas CAPA
- NCR Coordinator / ASME: review ASME (jika diberlakukan)
- Receiver Department Manager: assign PIC + disposition & menentukan butuh CAPA atau tidak
- PIC of CA (NCR): PIC untuk penanganan NCR (receiver-side)
- PIC of CAPA: PIC pelaksana CAPA

## NCR End-to-End
1. **Draft**
   - Dibuat oleh User (Finder).
2. **Submit**
   - User submit → status menjadi `Pending_Finder_Approval`.
3. **Finder Approval**
   - Finder Dept Manager approve → status menjadi `Pending_QC_Registration`.
   - Jika reject → kembali ke `Draft`.
4. **QC Registration**
   - QC Manager melakukan registrasi/validasi → status menjadi `Pending_ASME_Review`.
5. **ASME Review (opsional, sesuai proses perusahaan)**
   - NCR Coordinator/ASME approve → status menjadi `Sent_To_Receiver`.
6. **Receiver Handling**
   - Receiver Dept Manager mengisi disposition dan meng-assign `PIC of CA` (assigned PIC).
7. **Keputusan: Perlu CAPA?**
   - Jika perlu CAPA → dibuat CAPA dari NCR, NCR berpindah ke proses CAPA.
   - Jika tidak perlu CAPA → NCR dapat diproses hingga siap ditutup.
8. **Close NCR**
   - NCR ditutup (status `Closed`) setelah tindakan selesai (dan CAPA selesai jika ada).

## CAPA End-to-End
1. **Create CAPA (From NCR)**
   - Dibuat oleh Receiver Dept Manager / Admin / QC Manager (sesuai permission).
   - PIC CAPA wajib dipilih (assigned).
2. **In Progress**
   - PIC CAPA meng-update progress (0–100%).
3. **Pending Verification**
   - Saat progress 100%, CAPA masuk `Pending_Verification`.
4. **Verify (QC Manager)**
   - QC Manager verifikasi efektivitas → CAPA `Verified`.
5. **Close CAPA**
   - CAPA `Closed`.
6. **NCR Close**
   - NCR bisa ditutup setelah CAPA closed (jika CAPA dibuat).

## Diagram
- Mermaid file: [SOP_WORKFLOW_NCR_CAPA.mmd](file:///c:/xampp/htdocs/ncr-capa-management/docs/SOP_WORKFLOW_NCR_CAPA.mmd)

