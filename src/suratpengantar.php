<?php
session_start();
include 'backend/koneksi.php';

if (!isset($_SESSION['user'])) {
  header("Location: formlogin.php");
  exit;
}

if (!isset($_GET['id'])) {
  header("Location: manajemen-laporan.php");
  exit;
}

$id_mr = $_GET['id'];

// Ambil data laporan utama
$query = "SELECT lm.*, u.nama_unit as nama_unit_laporan 
          FROM laporan_mr lm 
          LEFT JOIN unit u ON lm.id_unit = u.id_unit 
          WHERE lm.id_mr = $id_mr";
$result = mysqli_query($koneksi, $query);
$laporan = mysqli_fetch_assoc($result);

// Ambil data perangkat
$query_perangkat = "SELECT * FROM perangkat WHERE id_mr = {$laporan['id_mr']}";
$result_perangkat = mysqli_query($koneksi, $query_perangkat);
$perangkats = [];
while ($perangkat = mysqli_fetch_assoc($result_perangkat)) {
    $perangkats[] = $perangkat;
}

// Ambil tahun dari tanggal laporan di database
$tahun = date('Y', strtotime($laporan['tanggal_laporan']));

mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Surat Pengantar - <?php echo htmlspecialchars($laporan['nomor_mr']); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @page {
      size: A4;
      margin: 20mm;
    }
    @media print {
      .no-print {
        display: none !important;
      }
      body {
        background: white !important;
        margin: 0;
        padding: 0;
        font-family: "Times New Roman", Times, serif !important;
      }
      .print-container {
        margin: 0;
        padding: 0;
        box-shadow: none;
        border: none;
        font-family: "Times New Roman", Times, serif !important;
      }
    }
    body {
      font-family: "Times New Roman", Times, serif;
    }
    .surat-info {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }
    .surat-info-left {
      flex: 1;
    }
    .surat-info-right {
      text-align: right;
    }
    .info-item {
      display: flex;
      margin-bottom: 4px;
    }
    .info-label {
      width: 80px;
      flex-shrink: 0;
    }
    .info-content {
      flex: 1;
    }
  </style>
  <script>
    // Auto print setelah halaman terbuka
    window.onload = function() {
      window.print();
    }
  </script>
</head>
<body class="font-serif bg-white">
  <div class="max-w-4xl mx-auto p-8 print-container">
    <!-- Kop Surat dengan Logo -->
    <div class="flex items-center justify-center border-b-2 border-black pb-4 mb-6">
      <div class="flex-shrink-0 mr-4">
        <img src="assets/uni.png" alt="Logo UNIPMA" class="w-20 h-20 object-contain" onerror="this.style.display='none'">
      </div>
      <div class="text-center">
        <h1 class="text-xl font-bold uppercase">UNIVERSITAS PGRI MADIUN</h1>
        <h2 class="text-lg font-bold">UPT KOMPUTER</h2>
        <p class="text-xs mt-1">Jl. Setiabudi No. 85 Madiun 63118 Telpon : 0351-462986 Fax : 0351-459400</p>
        <p class="text-xs">Website: http://komp.unipma.ac.id, Email: uptkomputer@unipma.ac.id</p>
      </div>
    </div>

    <!-- Nomor dan Tanggal Surat - Dengan spasi yang sejajar -->
    <div class="mb-6">
      <div class="surat-info">
        <div class="surat-info-left">
          <div class="info-item">
            <span class="info-label">Nomor</span>
            <span class="info-content">: <?php echo htmlspecialchars($laporan['nomor_surat']); ?>/D/UPT-KOMP/UNIPMA/<?php echo $tahun; ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Lampiran</span>
            <span class="info-content">: -</span>
          </div>
          <div class="info-item">
            <span class="info-label">Hal</span>
            <span class="info-content">: Laporan Pengecekan Komputer/Printer</span>
          </div>
        </div>
        <div class="surat-info-right">
          <p>Madiun, <?php echo date('d F Y', strtotime($laporan['tanggal_laporan'])); ?></p>
        </div>
      </div>
    </div>

    <!-- Alamat Tujuan -->
    <div class="mb-6">
      <p>Kepada :</p>
      <p>Yth. Wakil Rektor Bidang II</p>
      <p>UNIVERSITAS PGRI MADIUN</p>
      <p>di Tempat</p>
    </div>

    <!-- Isi Surat -->
    <div class="mb-6 text-justify">
      <p>Dengan hormat,</p>
      <p>
        Menindaklanjuti laporan pengecekan komputer/printer di: <u><?php echo htmlspecialchars($laporan['nama_unit_laporan']); ?></u>.
        Setelah diadakan pengecekan oleh bagian <i>M-R (Maintenance and Repair)</i> UPT Komputer,
        dilaporkan beberapa kerusakan yang terjadi beserta solusinya sebagaimana dalam lampiran
        laporan ini.
      </p>
      
      <?php if (!empty($perangkats)): ?>
      <p>Perangkat yang diperiksa:</p>
      <ul class="list-disc list-inside ml-4 mb-4">
        <?php foreach ($perangkats as $perangkat): ?>
          <li><?php echo htmlspecialchars($perangkat['nama_perangkat']); ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      
      <p>Demikian laporan ini, selanjutnya kami mohon petunjuk lebih lanjut untuk penyelesaiannya.</p>
    </div>

    <!-- Tanda Tangan -->
    <div class="mt-20 text-right">
      <div>
        <p>Kepala,</p>
        <div class="mt-16">
          <p><strong>Andria, S.Kom., M.Kom.</strong></p>
          <p>NIK: 110682</p>
        </div>
      </div>
    </div>

    <!-- Tombol untuk print manual -->
    <div class="no-print text-center mt-8 border-t pt-4">
      <p class="mb-4">Klik tombol di bawah untuk print surat pengantar:</p>
      <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg transition duration-200 mr-2">
        Print Surat
      </button>
      <button onclick="window.close()" class="bg-red-600 hover:bg-red-700 text-white py-2 px-6 rounded-lg transition duration-200">
        Tutup
      </button>
    </div>
  </div>
</body>
</html>