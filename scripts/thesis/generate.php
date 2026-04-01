<?php

declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html as PhpWordHtml;
use Smalot\PdfParser\Parser as PdfParser;

require __DIR__ . '/../../vendor/autoload.php';

$root = realpath(__DIR__ . '/../..');
if (!$root) {
    fwrite(STDERR, "Project root not found.\n");
    exit(2);
}

function joinPath(string $base, string $rel): string
{
    $rel = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
    return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($rel, DIRECTORY_SEPARATOR);
}

$opt = getopt('', [
    'pedoman-proposal::',
    'pedoman-penelitian-bersama::',
    'bab1::',
    'bab2::',
    'bab3::',
    'proposal-revisi-dospem::',
    'judul::',
]);

$inputs = [
    'pedoman_proposal' => $opt['pedoman-proposal'] ?? joinPath($root, 'local/Penulisan dan Pedoman/Pedoman/2. Pedoman Proposal Penelitian.pdf'),
    'pedoman_penelitian_bersama' => $opt['pedoman-penelitian-bersama'] ?? joinPath($root, 'local/Penulisan dan Pedoman/Pedoman/3. Pedoman Penelitian Bersama.pdf'),
    'bab1' => $opt['bab1'] ?? joinPath($root, 'local/Penulisan dan Pedoman/Penulisan/BAB I.pdf'),
    'bab2' => $opt['bab2'] ?? joinPath($root, 'local/Penulisan dan Pedoman/Penulisan/BAB II.pdf'),
    'bab3' => $opt['bab3'] ?? joinPath($root, 'local/Penulisan dan Pedoman/Penulisan/BAB III.pdf'),
    'proposal_revisi_dospem' => $opt['proposal-revisi-dospem'] ?? joinPath($root, 'local/Penulisan dan Pedoman/Penulisan/Laporan Proposal Penelitian_ Wawan Warsito 312210233 (Revisi from dospem 2) 05-01-2026.pdf'),
];

foreach ($inputs as $key => $path) {
    if (!is_file($path)) {
        fwrite(STDERR, "Missing file for {$key}: {$path}\n");
        exit(2);
    }
}

$outDir = __DIR__ . '/../../docs/thesis/output';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$pdf = new PdfParser();
function pdfText(PdfParser $pdf, string $path): string
{
    try {
        $doc = $pdf->parseFile($path);
        $text = $doc->getText();
        $text = preg_replace("/[ \t]+/", " ", $text ?? '');
        $text = preg_replace("/\r\n|\r/", "\n", $text ?? '');
        $text = preg_replace("/\n{3,}/", "\n\n", $text ?? '');
        return trim($text ?? '');
    } catch (Throwable $e) {
        return '';
    }
}

$pedomanProposal = pdfText($pdf, $inputs['pedoman_proposal']);
$pedomanSkripsi = pdfText($pdf, $inputs['pedoman_penelitian_bersama']);
$bab1 = pdfText($pdf, $inputs['bab1']);
$bab2 = pdfText($pdf, $inputs['bab2']);
$bab3 = pdfText($pdf, $inputs['bab3']);
$proposalRevisi = pdfText($pdf, $inputs['proposal_revisi_dospem']);

function buildBab1Revisi(string $judul): string
{
    return <<<HTML
<h1>BAB I<br/>PENDAHULUAN</h1>
<h2>1.1 Latar Belakang</h2>
<p>Industri fabrikasi stainless steel memiliki tuntutan kualitas yang tinggi karena produk yang dihasilkan digunakan pada berbagai kebutuhan industri. Ketidaksesuaian (non-conformance) yang terjadi pada material, proses, maupun hasil fabrikasi harus dikelola secara sistematis agar tidak menimbulkan pemborosan biaya, keterlambatan, maupun risiko penurunan kepuasan pelanggan. Dalam praktik di PT. Topsystem Asia Base, pencatatan dan penelusuran non-conformance masih berpotensi mengalami keterlambatan informasi, kesulitan pelacakan status, serta kurangnya keterhubungan antar departemen yang terlibat.</p>
<p>Non Conformance Report (NCR) merupakan dokumen formal untuk mencatat temuan ketidaksesuaian, sedangkan Corrective and Preventive Action (CAPA) adalah rangkaian tindakan untuk mengatasi akar masalah dan mencegah terulangnya ketidaksesuaian. Agar proses NCR–CAPA berjalan efektif, diperlukan sistem yang mampu mengatur alur kerja lintas departemen (Finder dan Receiver), persetujuan berjenjang, penugasan PIC, notifikasi, serta audit trail. Oleh karena itu, penelitian ini mengimplementasikan sistem manajemen NCR–CAPA berbasis web dengan studi kasus PT. Topsystem Asia Base.</p>
<h2>1.2 Identifikasi Masalah</h2>
<ol>
  <li>Pencatatan dan pemantauan status NCR–CAPA belum terintegrasi lintas departemen.</li>
  <li>Proses approval dan penugasan PIC berpotensi terlambat karena minimnya notifikasi dan tracking.</li>
  <li>Dokumentasi bukti (attachment) dan histori perubahan belum terdokumentasi rapi untuk kebutuhan audit.</li>
  <li>Pelaporan ringkas (dashboard/reports) untuk manajemen belum tersedia secara real-time.</li>
</ol>
<h2>1.3 Rumusan Masalah</h2>
<ol>
  <li>Bagaimana mengimplementasikan sistem manajemen NCR–CAPA berbasis web yang mendukung alur kerja lintas departemen di PT. Topsystem Asia Base?</li>
  <li>Bagaimana merancang workflow approval, assignment PIC, serta keterkaitan NCR–CAPA agar proses lebih terkendali dan dapat ditelusuri?</li>
  <li>Bagaimana menyediakan dashboard dan laporan untuk monitoring kualitas secara cepat dan akurat?</li>
</ol>
<h2>1.4 Batasan Masalah</h2>
<ol>
  <li>Ruang lingkup sistem meliputi modul NCR, CAPA, dashboard, notifikasi, dan audit trail.</li>
  <li>Metodologi pengembangan yang digunakan adalah iterative/incremental development.</li>
  <li>Integrasi eksternal (mis. email gateway perusahaan) dibahas sebagai saran, tidak wajib diimplementasikan penuh.</li>
</ol>
<h2>1.5 Tujuan Penelitian</h2>
<ol>
  <li>Mengimplementasikan sistem manajemen NCR–CAPA berbasis web sesuai kebutuhan bisnis PT. Topsystem Asia Base.</li>
  <li>Membangun workflow NCR–CAPA yang jelas, terdokumentasi, dan dapat ditelusuri.</li>
  <li>Menyediakan informasi monitoring (dashboard & laporan) untuk mendukung pengambilan keputusan.</li>
</ol>
<h2>1.6 Manfaat Penelitian</h2>
<ol>
  <li><b>Perusahaan:</b> mempercepat penanganan ketidaksesuaian, meningkatkan keterlacakan, dan mendukung audit.</li>
  <li><b>Departemen terkait:</b> memperjelas tanggung jawab Finder/Receiver dan PIC.</li>
  <li><b>Peneliti:</b> menerapkan konsep sistem informasi kualitas pada studi kasus nyata.</li>
</ol>
<h2>1.7 Sistematika Penulisan</h2>
<p>Penulisan proposal penelitian ini disusun sebagai berikut: BAB I Pendahuluan, BAB II Tinjauan Pustaka, BAB III Metodologi Penelitian dan Perancangan Sistem.</p>
HTML;
}

function buildBab2Revisi(): string
{
    return <<<HTML
<h1>BAB II<br/>TINJAUAN PUSTAKA</h1>
<h2>2.1 Konsep Dasar Non Conformance Report (NCR)</h2>
<p>NCR adalah dokumen formal untuk mencatat ketidaksesuaian terhadap spesifikasi, standar, atau persyaratan pelanggan. NCR bertujuan memastikan temuan terdokumentasi, dianalisis, dan ditindaklanjuti hingga tuntas.</p>
<h2>2.2 Konsep Corrective and Preventive Action (CAPA)</h2>
<p>CAPA merupakan tindakan perbaikan dan pencegahan untuk menghilangkan penyebab ketidaksesuaian agar tidak terulang. CAPA umumnya memuat analisis akar masalah (mis. 5 Why/Fishbone), rencana tindakan, PIC, target penyelesaian, serta verifikasi efektivitas.</p>
<h2>2.3 Manajemen Mutu dan Keterlacakan</h2>
<p>ISO 9001:2015 menekankan pentingnya kontrol proses, dokumentasi, tindakan korektif, dan perbaikan berkelanjutan. Sistem NCR–CAPA mendukung prinsip tersebut melalui workflow, audit trail, dan pelaporan.</p>
<h2>2.4 Sistem Informasi Berbasis Web</h2>
<p>Sistem berbasis web memudahkan akses lintas departemen dan menyediakan informasi real-time. Penerapan role-based access control (RBAC) diperlukan untuk membatasi akses sesuai peran.</p>
<h2>2.5 Metodologi Pengembangan Iterative/Incremental</h2>
<p>Metode iterative/incremental mengembangkan sistem melalui siklus berulang: perencanaan, analisis, desain, implementasi, dan evaluasi. Setiap iterasi menghasilkan peningkatan fungsional yang dapat divalidasi dengan pengguna.</p>
<h2>2.6 Penelitian Terkait</h2>
<p>Penelitian terkait umumnya membahas penerapan sistem kualitas berbasis web untuk mempercepat alur pelaporan non-conformance, memperkuat kontrol CAPA, dan meningkatkan pelaporan manajemen. Pada penelitian ini, fokus diarahkan pada kolaborasi lintas departemen (Finder–Receiver) serta dashboard multi-perspektif.</p>
HTML;
}

function buildBab3Revisi(): string
{
    return <<<HTML
<h1>BAB III<br/>METODOLOGI PENELITIAN DAN PERANCANGAN SISTEM</h1>
<h2>3.1 Metode Penelitian</h2>
<p>Penelitian menggunakan pendekatan rekayasa perangkat lunak dengan metodologi iterative/incremental development. Tahapan dilakukan berulang pada setiap iterasi untuk menghasilkan modul yang dapat diuji dan divalidasi.</p>
<h2>3.2 Teknik Pengumpulan Data</h2>
<ol>
  <li><b>Observasi:</b> mengamati proses penanganan NCR–CAPA pada departemen terkait.</li>
  <li><b>Wawancara:</b> dengan QC Manager, Department Manager, dan user terkait untuk menggali kebutuhan.</li>
  <li><b>Studi Dokumen:</b> mempelajari format NCR, CAPA, dan kebijakan internal perusahaan.</li>
</ol>
<h2>3.3 Analisis Kebutuhan</h2>
<h3>3.3.1 Kebutuhan Fungsional</h3>
<ul>
  <li>CRUD NCR, submit, approval berjenjang, routing, assign PIC, close.</li>
  <li>CRUD CAPA dari NCR, progress tracking, verification, close.</li>
  <li>Dashboard (company/department/personal), notifikasi, laporan, dan audit trail.</li>
</ul>
<h3>3.3.2 Kebutuhan Non-Fungsional</h3>
<ul>
  <li>Keamanan: autentikasi dan RBAC.</li>
  <li>Kinerja: pagination dan query teroptimasi.</li>
  <li>Keterlacakan: aktivitas tercatat pada audit trail.</li>
</ul>
<h2>3.4 Perancangan Sistem</h2>
<h3>3.4.1 Arsitektur</h3>
<p>Sistem menggunakan arsitektur client–server. Backend menyediakan REST API, sedangkan frontend menyediakan antarmuka pengguna.</p>
<h3>3.4.2 Perancangan Database (Konseptual)</h3>
<p>Entitas utama meliputi User, Role, Department, NCR, CAPA, Attachment, Notification, dan ActivityLog. Relasi kunci: NCR–CAPA (1:1), NCR–Attachment (1:N), serta ActivityLog untuk audit trail.</p>
<h3>3.4.3 Perancangan Workflow</h3>
<p>Workflow NCR–CAPA dirancang untuk memastikan persetujuan berjenjang dan penugasan PIC sebelum tindakan dilakukan, serta verifikasi efektivitas CAPA oleh QC Manager sebelum penutupan.</p>
<h2>3.5 Rencana Pengujian</h2>
<p>Pengujian meliputi pengujian fungsional (unit/feature test), pengujian integrasi API, serta pengujian hak akses berdasarkan role.</p>
HTML;
}

function extractRules(string $pedomanText): array
{
    $rules = [];
    $candidates = [
        'margin' => '/margin.{0,30}/i',
        'spasi' => '/spasi.{0,40}/i',
        'font' => '/(times new roman|arial|calibri).{0,20}/i',
        'ukuran' => '/(ukuran|size).{0,30}(10|11|12|13|14)/i',
        'sistematika' => '/sistematika.{0,60}/i',
    ];

    foreach ($candidates as $name => $rx) {
        if (preg_match_all($rx, $pedomanText, $m)) {
            $rules[$name] = array_values(array_unique(array_map('trim', $m[0])));
        }
    }
    return $rules;
}

$rulesProposal = extractRules($pedomanProposal);
$rulesSkripsi = extractRules($pedomanSkripsi);

$judul = 'IMPLEMENTASI SISTEM MANAJEMEN NON CONFORMANCE REPORT DAN CORRECTIVE AND PREVENTIVE ACTION BERBASIS WEB PADA INDUSTRI FABRIKASI STAINLESS STEEL (STUDI KASUS PT. TOPSYSTEM ASIA BASE)';

function cleanTitle(string $t): string
{
    $t = preg_replace('/\s+/', ' ', trim($t));
    $t = str_replace('STELL', 'STEEL', $t);
    return $t;
}

$judul = cleanTitle($judul);
$judul = isset($opt['judul']) && trim((string) $opt['judul']) !== '' ? cleanTitle((string) $opt['judul']) : $judul;

function buildBab4Draft(): string
{
    return <<<HTML
<h1>BAB IV<br/>IMPLEMENTASI DAN PENGUJIAN SISTEM</h1>
<h2>4.1 Lingkungan Implementasi</h2>
<p>Implementasi sistem dilakukan dengan arsitektur client–server. Backend menggunakan Laravel (PHP 8.2+) dan frontend menggunakan React + Vite. Autentikasi API menggunakan Laravel Sanctum.</p>
<h2>4.2 Implementasi Fitur Utama</h2>
<h3>4.2.1 Implementasi Modul NCR</h3>
<ul>
  <li>Pembuatan NCR (Draft) dan submit untuk approval berjenjang.</li>
  <li>Persetujuan berjenjang (Finder Manager → QC Manager → NCR Coordinator/ASME).</li>
  <li>Routing ke Receiver Department, assign PIC of CA, disposition, dan cost.</li>
  <li>Attachment upload dan audit trail (activity log).</li>
  <li>QR Code pada halaman print NCR untuk pelacakan.</li>
 </ul>
<h3>4.2.2 Implementasi Modul CAPA</h3>
<ul>
  <li>Pembuatan CAPA dari NCR, termasuk RCA (5 Why/Fishbone) dan penetapan PIC serta target tanggal.</li>
  <li>Update progress, hingga status Pending Verification.</li>
  <li>Verifikasi efektivitas oleh QC Manager dan penutupan CAPA.</li>
 </ul>
<h3>4.2.3 Implementasi Dashboard</h3>
<ul>
  <li>Company Dashboard (khusus Admin/QC Manager).</li>
  <li>Department Dashboard (milik departemen user).</li>
  <li>Personal Dashboard (task/approval per role).</li>
 </ul>
<h2>4.3 Pengujian</h2>
<h3>4.3.1 Pengujian Fungsional (Unit & Feature Test)</h3>
<p>Pengujian dilakukan menggunakan PHPUnit. Hasil pengujian menunjukkan seluruh test suite berhasil dijalankan.</p>
<p><b>Hasil:</b> 79 passed.</p>
<h3>4.3.2 Pengujian Hak Akses</h3>
<p>Pengujian hak akses dilakukan untuk memastikan Company Dashboard hanya dapat diakses Admin/QC Manager, sedangkan departemen dan personal dashboard mengikuti role.</p>
HTML;
}

function buildBab5Draft(): string
{
    return <<<HTML
<h1>BAB V<br/>KESIMPULAN DAN SARAN</h1>
<h2>5.1 Kesimpulan</h2>
<ol>
  <li>Sistem NCR–CAPA berbasis web berhasil diimplementasikan untuk mendukung kolaborasi lintas departemen melalui alur Finder–Receiver.</li>
  <li>Workflow approval berjenjang, assignment PIC, dan relasi NCR–CAPA dapat memperjelas akuntabilitas penyelesaian ketidaksesuaian.</li>
  <li>Dashboard multi-perspektif (company/department/personal) membantu monitoring status dan prioritas penyelesaian.</li>
  <li>Audit trail dan notifikasi meningkatkan keterlacakan serta respons terhadap tenggat waktu.</li>
 </ol>
<h2>5.2 Saran</h2>
<ol>
  <li>Menambahkan laporan statistik lanjutan (mis. cost trend per kategori/department) untuk mendukung keputusan manajemen.</li>
  <li>Mengintegrasikan email gateway perusahaan agar notifikasi email berjalan konsisten.</li>
  <li>Melakukan UAT formal per departemen dan menyusun SOP final berbasis hasil UAT.</li>
 </ol>
HTML;
}

function wrapHtml(string $title, string $body): string
{
    $css = <<<CSS
body { font-family: "Times New Roman", serif; font-size: 12pt; line-height: 1.5; }
h1 { font-size: 14pt; text-align: center; }
h2 { font-size: 12pt; margin-top: 18px; }
h3 { font-size: 12pt; margin-top: 12px; }
p { text-align: justify; }
CSS;
    $titleEsc = htmlspecialchars($title, ENT_QUOTES);
    return "<!doctype html><html><head><meta charset=\"utf-8\"><title>{$titleEsc}</title><style>{$css}</style></head><body>{$body}</body></html>";
}

function savePdf(string $html, string $outPath): void
{
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    file_put_contents($outPath, $dompdf->output());
}

function saveDocxFromHtml(string $html, string $outPath): void
{
    $phpWord = new PhpWord();
    $phpWord->setDefaultFontName('Times New Roman');
    $phpWord->setDefaultFontSize(12);
    $section = $phpWord->addSection();
    PhpWordHtml::addHtml($section, $html, false, false);
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($outPath);
}

$reviewMd = [];
$reviewMd[] = "# Review & Koreksi (Ringkas)";
$reviewMd[] = "";
$reviewMd[] = "## Judul";
$reviewMd[] = "- Judul dibakukan: **{$judul}** (perbaikan typo: STELL → STEEL).";
$reviewMd[] = "";
$reviewMd[] = "## Catatan Pedoman (hasil ekstraksi otomatis)";
$reviewMd[] = "- Pedoman Proposal: " . (empty($rulesProposal) ? "tidak terdeteksi aturan spesifik (perlu cek manual jika ekstraksi PDF kurang rapi)." : "terdeteksi beberapa kandidat aturan.");
$reviewMd[] = "- Pedoman Skripsi/Penelitian Bersama: " . (empty($rulesSkripsi) ? "tidak terdeteksi aturan spesifik (perlu cek manual jika ekstraksi PDF kurang rapi)." : "terdeteksi beberapa kandidat aturan.");
$reviewMd[] = "";
$reviewMd[] = "## BAB I–BAB III (input)";
$reviewMd[] = "- BAB I length: " . strlen($bab1) . " chars";
$reviewMd[] = "- BAB II length: " . strlen($bab2) . " chars";
$reviewMd[] = "- BAB III length: " . strlen($bab3) . " chars";
$reviewMd[] = "";
$reviewMd[] = "## Rekomendasi Koreksi Utama";
$reviewMd[] = "- Pastikan konsistensi istilah: NCR, CAPA, Finder Dept, Receiver Dept, PIC, Approval, Verification.";
$reviewMd[] = "- Pastikan metodologi yang dipakai tertulis konsisten: Iterative/Incremental Development (boleh ditulis 'mengadopsi praktik Agile' tanpa klaim Scrum formal).";
$reviewMd[] = "- Pastikan ruang lingkup: modul NCR, CAPA, dashboard, notifikasi, report, audit trail.";
$reviewMd[] = "";
$reviewMd[] = "## Referensi Revisi Dosen Pembimbing";
$reviewMd[] = "- File referensi revisi (ekstraksi): " . strlen($proposalRevisi) . " chars";
$reviewMd[] = "";
$reviewMdPath = $outDir . '/REVIEW_NOTES.md';
file_put_contents($reviewMdPath, implode("\n", $reviewMd));

$bab45HtmlBody = buildBab4Draft() . buildBab5Draft();
$bab45Html = wrapHtml('Draft BAB IV & BAB V', $bab45HtmlBody);

$outDocx = $outDir . '/DRAFT_BAB_IV_V.docx';
$outPdf = $outDir . '/DRAFT_BAB_IV_V.pdf';

saveDocxFromHtml($bab45HtmlBody, $outDocx);
savePdf($bab45Html, $outPdf);

$bab123Body = buildBab1Revisi($judul) . buildBab2Revisi() . buildBab3Revisi();
$bab123Html = wrapHtml('Proposal BAB I-III (Revisi Draft)', $bab123Body);
$outDocx123 = $outDir . '/PROPOSAL_BAB_I_III_REVISI.docx';
$outPdf123 = $outDir . '/PROPOSAL_BAB_I_III_REVISI.pdf';
saveDocxFromHtml($bab123Body, $outDocx123);
savePdf($bab123Html, $outPdf123);

echo "OK\n";
echo "Wrote:\n";
echo "- {$reviewMdPath}\n";
echo "- {$outDocx}\n";
echo "- {$outPdf}\n";
echo "- {$outDocx123}\n";
echo "- {$outPdf123}\n";
