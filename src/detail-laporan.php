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

// Logika untuk menentukan URL tujuan berdasarkan session
$url_kembali = 'formlogin.php';
if (isset($_SESSION['user']['Level'])) { 
    if ($_SESSION['user']['Level'] == 'admin') {
        $url_kembali = 'manajemen-laporan.php';
    } elseif ($_SESSION['user']['Level'] == 'user') {
        $url_kembali = 'laporan.php';
    }
}

$level = $_SESSION['user']['Level'];
$id_mr = $_GET['id'];

// Ambil data laporan utama
$query = "SELECT lm.*, u.nama_unit as nama_unit_laporan 
          FROM laporan_mr lm 
          LEFT JOIN unit u ON lm.id_unit = u.id_unit 
          WHERE lm.id_mr = $id_mr";
$result = mysqli_query($koneksi, $query);
$laporan = mysqli_fetch_assoc($result);

// Ambil data lampiran
$query_lampiran = "SELECT * FROM lampiran WHERE id_mr = $id_mr";
$result_lampiran = mysqli_query($koneksi, $query_lampiran);
$lampiran = mysqli_fetch_assoc($result_lampiran);

// Ambil data keluhan
$query_keluhan = "SELECT * FROM keluhan WHERE id_mr = $id_mr";
$result_keluhan = mysqli_query($koneksi, $query_keluhan);
$keluhans = [];
while ($keluhan = mysqli_fetch_assoc($result_keluhan)) {
    $keluhans[] = $keluhan;
}

// Ambil data perbaikan
$query_perbaikan = "SELECT p.*, pt.nama_petugas 
                    FROM perbaikan p 
                    LEFT JOIN petugas pt ON p.id_petugas = pt.id_petugas 
                    WHERE p.id_mr = $id_mr";
$result_perbaikan = mysqli_query($koneksi, $query_perbaikan);
$perbaikans = [];
while ($perbaikan = mysqli_fetch_assoc($result_perbaikan)) {
    $perbaikans[] = $perbaikan;
}

// Ambil data perangkat
$query_perangkat = "SELECT * FROM perangkat WHERE id_mr = {$laporan['id_mr']}";
$result_perangkat = mysqli_query($koneksi, $query_perangkat);
$perangkats = [];
while ($perangkat = mysqli_fetch_assoc($result_perangkat)) {
    $perangkats[] = $perangkat;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/1.png" />
  <title>Detail Laporan | Sistem MR UPT Komputer</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans min-h-screen">
  <!-- Navbar -->
  
  <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md mt-8">
    <!-- Container untuk print -->
    <div class="bg-white p-6 border border-gray-200 rounded">
      <!-- Header untuk print -->
      <div class="text-center border-b-2 border-gray-300 pb-4 mb-6">
        <h2 class="text-2xl font-bold">UPT KOMPUTER UNIPMA</h2>
        <p class="text-gray-600 text-lg">LAPORAN MAINTENANCE DAN REPAIR</p>
      </div>

      <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
          <label class="font-semibold text-gray-700">Nomor MR:</label>
          <p class="font-bold text-gray-900"><?php echo htmlspecialchars($laporan['nomor_mr']); ?></p>
        </div>
        <div>
          <label class="font-semibold text-gray-700">Nomor Surat:</label>
          <p class="text-gray-900"><?php echo htmlspecialchars($laporan['nomor_surat']) ? $row['nomor_surat'] : '<span class="text-gray-400">Tidak Ada</span>'; ?></p>
        </div>
        <div>
          <label class="font-semibold text-gray-700">Keperluan Surat:</label>
          <p class="text-gray-900"><?php echo htmlspecialchars($laporan['keperluan_surat']) ? $row['keperluan_surat'] : '<span class="text-gray-400">Tidak Ada</span>'; ?></p>
        </div>
        <div>
          <label class="font-semibold text-gray-700">Tanggal Laporan:</label>
          <p class="text-gray-900"><?php echo date('d M Y', strtotime($laporan['tanggal_laporan'])); ?></p>
        </div>
        <div>
    <label class="font-semibold text-gray-700">Tanggal Pengecekan:</label>
    <p class="text-gray-900">
        <?php 
        $tanggal_pengecekan = $laporan['tanggal_pengecekan'];
        if ($tanggal_pengecekan && $tanggal_pengecekan != '0000-00-00' && $tanggal_pengecekan != '0000-00-00 00:00:00') {
            echo date('d M Y', strtotime($tanggal_pengecekan));
        } else {
            echo '<span class="text-gray-400">Belum dicek</span>';
        }
        ?>
    </p>
</div>
        <div>
          <label class="font-semibold text-gray-700">Unit:</label>
          <p class="text-gray-900"><?php echo htmlspecialchars($laporan['nama_unit_laporan']); ?></p>
        </div>
        <div>
  <label class="font-semibold text-gray-700">Status:</label>
  <?php
  $status = $laporan['status'];
  $status_class = '';
  $icon = '';

  switch ($status) {
    case 'Antrian':
      $status_class = 'bg-yellow-100 text-yellow-800';
      $icon = 'fa-clock';
      break;
    case 'Diproses':
      $status_class = 'bg-blue-100 text-blue-800';
      $icon = 'fa-tools';
      break;
    case 'Tertunda':
      $status_class = 'bg-orange-100 text-orange-800';
      $icon = 'fa-pause-circle';
      break;
    case 'Selesai':
      $status_class = 'bg-green-100 text-green-800';
      $icon = 'fa-check-circle';
      break;
    case 'Proses Dibeli Perangkat oleh BAU':
      $status_class = 'bg-purple-100 text-purple-800';
      $icon = 'fa-shopping-cart';
      break;
    case 'Perangkat Sudah Dibeli oleh BAU':
      $status_class = 'bg-indigo-100 text-indigo-800';
      $icon = 'fa-box';
      break;
    case 'Proses Dibeli Perangkat oleh UPT':
      $status_class = 'bg-pink-100 text-pink-800';
      $icon = 'fa-shopping-cart';
      break;
    case 'Perangkat Sudah Dibeli oleh UPT':
      $status_class = 'bg-emerald-300 text-emerald-900';
      $icon = 'fa-box';
      break;
    default:
      $status_class = 'bg-gray-100 text-gray-800';
      $icon = 'fa-question-circle';
      break;
  }
  ?>
  <p class="px-3 py-1 rounded-full text-sm font-medium inline-flex items-center gap-2 <?php echo $status_class; ?>">
    <i class="fas <?php echo $icon; ?>"></i>
    <?php echo htmlspecialchars($status); ?>
  </p>
</div>

      </div>

      <div class="mb-6">
        <label class="font-semibold text-gray-700 block mb-2">Hasil Pengecekan:</label>
        <div class="bg-gray-50 p-4 rounded border border-gray-200">
          <?php echo nl2br(htmlspecialchars($laporan['hasil_pengecekan'])) ? $row['hasil_pengecekan'] : '<span class="text-gray-400">Belum dicek</span>'; ?>
        </div>
      </div>

      <?php if (!empty($perangkats)): ?>
      <div class="mb-6">
        <label class="font-semibold text-gray-700 block mb-2">Perangkat:</label>
        <ul class="list-disc list-inside bg-gray-50 p-4 rounded border border-gray-200">
          <?php foreach ($perangkats as $perangkat): ?>
            <li class="text-gray-900"><?php echo htmlspecialchars($perangkat['nama_perangkat']); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($keluhans)): ?>
      <div class="mb-6">
        <label class="font-semibold text-gray-700 block mb-2">Keluhan:</label>
        <ul class="list-disc list-inside bg-gray-50 p-4 rounded border border-gray-200">
          <?php foreach ($keluhans as $keluhan): ?>
            <li class="text-gray-900"><?php echo htmlspecialchars($keluhan['deskripsi_keluhan']); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($perbaikans)): ?>
      <div class="mb-6">
        <label class="font-semibold text-gray-700 block mb-2">Perbaikan:</label>
        <ul class="list-disc list-inside bg-gray-50 p-4 rounded border border-gray-200">
          <?php foreach ($perbaikans as $perbaikan): ?>
            <li class="mb-2">
              <span class="font-medium text-gray-900"><?php echo htmlspecialchars($perbaikan['deskripsi_perbaikan']); ?></span>
              <div class="text-sm text-gray-600 mt-1">
                Oleh: <?php echo htmlspecialchars($perbaikan['nama_petugas']); ?> | 
                Tanggal: <?php echo date('d M Y', strtotime($laporan['tanggal_pengecekan'])); ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

    </div>

    <!-- Tombol dipindah ke bawah dan ditambahkan Lihat Lampiran -->
    <div class="flex justify-center gap-3 mt-8 print:hidden flex-wrap">
        <a href="suratpengantar.php?id=<?php echo $id_mr; ?>" 
         target="_blank"
         class="bg-purple-600 hover:bg-purple-700 text-white py-3 px-6 rounded-lg flex items-center transition duration-200">
        <i class="fas fa-file-alt mr-2"></i> Print Surat Pengantar
      </a>
      <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-lg flex items-center transition duration-200">
        <i class="fas fa-print mr-2"></i> Print Laporan
      </button>
      
      <?php if ($lampiran): ?>
      <a href="../src/<?php echo $lampiran['nama_file']; ?>" 
         target="_blank" 
         class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg flex items-center transition duration-200">
        <i class="fas fa-paperclip mr-2"></i> Lihat Lampiran
      </a>
      <?php endif; ?>
      
      <a href="<?php echo htmlspecialchars($url_kembali); ?>" class="bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg inline-flex items-center transition duration-200">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
    </a>
        
      <?php if ($level == 'admin'): ?>
        <a href="edit-laporan.php?id=<?php echo $id_mr; ?>" class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg flex items-center transition duration-200">
          <i class="fas fa-edit mr-2"></i> Edit Laporan
        </a>
      <?php endif; ?>
    </div>
  </div>

  <?php
  // Tutup koneksi database
  mysqli_close($koneksi);
  ?>
</body>
</html>