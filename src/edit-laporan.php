<?php
include 'backend/koneksi.php';
session_start();

$nama  = $_SESSION['user']['nama_user'];
$level = $_SESSION['user']['Level'];

if (!isset($_SESSION['user'])) {
  header("Location: formlogin.php");
  exit;
}

if ($_SESSION['user']['Level'] != 'admin') {
  // Jika bukan admin, redirect ke halaman lain atau tampilkan error
  header("Location: unauthorized.php"); // atau dashboard.php
  exit;
}



if (!isset($_GET['id'])) {
  header("Location: manajemen-laporan.php");
  exit;
}

$id_mr = $_GET['id'];

// Ambil data laporan utama
$query = "SELECT * FROM laporan_mr WHERE id_mr = $id_mr";
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
while ($row = mysqli_fetch_assoc($result_keluhan)) {
  $keluhans[] = $row['deskripsi_keluhan'];
}

// Ambil data perbaikan
$query_perbaikan = "SELECT * FROM perbaikan WHERE id_mr = $id_mr";
$result_perbaikan = mysqli_query($koneksi, $query_perbaikan);
$perbaikans = [];
while ($row = mysqli_fetch_assoc($result_perbaikan)) {
  $perbaikans[] = $row['deskripsi_perbaikan'];
}

// Ambil data perangkat
$query_perangkat = "SELECT * FROM perangkat WHERE id_mr = {$laporan['id_mr']}";
$result_perangkat = mysqli_query($koneksi, $query_perangkat);
$perangkats = [];
while ($row = mysqli_fetch_assoc($result_perangkat)) {
  $perangkats[] = $row['nama_perangkat'];
}

// Ambil id_petugas dari data perbaikan yang sudah ada
$query_petugas_edit = "SELECT id_petugas FROM perbaikan WHERE id_mr = $id_mr LIMIT 1";
$result_petugas_edit = mysqli_query($koneksi, $query_petugas_edit);
$petugas_data = mysqli_fetch_assoc($result_petugas_edit);
$id_petugas = $petugas_data['id_petugas'] ?? ''; // Default value jika tidak ada

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil data dari form
  $nomor = $_POST['nomorSurat'];
  $keperluan = $_POST['keperluanSurat'];
  $tgl_lap = $_POST['tanggalLaporan'];
  $tgl_cek = $_POST['tanggalPengecekan'];
  $status = $_POST['status'];
  $id_unit = $_POST['id_unit'];
  $id_petugas = $_POST['id_petugas'];

  // Ambil array multi input
  $perangkat = $_POST['perangkat'] ?? [];
  $keluhan = $_POST['keluhan'] ?? [];
  $hasilPengecekan = $_POST['hasilPengecekan'] ?? [];
  $perbaikan = $_POST['perbaikan'] ?? [];

  // Gabungkan hasil pengecekan jadi string
  $hasil = implode(", ", $hasilPengecekan);

  // Mulai transaction
  $koneksi->begin_transaction();

  try {
    // Update tabel laporan_mr
    $update_stmt = $koneksi->prepare("UPDATE laporan_mr 
      SET nomor_surat = ?, keperluan_surat = ?, tanggal_laporan = ?, 
          tanggal_pengecekan = ?, hasil_pengecekan = ?, status = ?, id_unit = ?
      WHERE id_mr = ?");

    $update_stmt->bind_param(
      "ssssssii",
      $nomor,
      $keperluan,
      $tgl_lap,
      $tgl_cek,
      $hasil,
      $status,
      $id_unit,
      $id_mr
    );

    if (!$update_stmt->execute()) {
      throw new Exception("Gagal mengupdate laporan: " . $update_stmt->error);
    }

    $update_stmt->close();

    // Hapus data lama dan insert data baru untuk keluhan
    $delete_keluhan = $koneksi->prepare("DELETE FROM keluhan WHERE id_mr = ?");
    $delete_keluhan->bind_param("i", $id_mr);
    $delete_keluhan->execute();
    $delete_keluhan->close();

    foreach ($keluhan as $k) {
      if (!empty(trim($k))) {
        $insert_keluhan = $koneksi->prepare("INSERT INTO keluhan (id_mr, deskripsi_keluhan) VALUES (?, ?)");
        $insert_keluhan->bind_param("is", $id_mr, $k);
        if (!$insert_keluhan->execute()) {
          throw new Exception("Gagal menyimpan keluhan: " . $insert_keluhan->error);
        }
        $insert_keluhan->close();
      }
    }

    // Hapus data lama dan insert data baru untuk perbaikan
    $delete_perbaikan = $koneksi->prepare("DELETE FROM perbaikan WHERE id_mr = ?");
    $delete_perbaikan->bind_param("i", $id_mr);
    $delete_perbaikan->execute();
    $delete_perbaikan->close();

    foreach ($perbaikan as $p) {
      if (!empty(trim($p))) {
        $insert_perbaikan = $koneksi->prepare("INSERT INTO perbaikan (id_mr, id_petugas, deskripsi_perbaikan) VALUES (?, ?, ?)");
        $insert_perbaikan->bind_param("iis", $id_mr, $id_petugas, $p);
        if (!$insert_perbaikan->execute()) {
          throw new Exception("Gagal menyimpan perbaikan: " . $insert_perbaikan->error);
        }
        $insert_perbaikan->close();
      }
    }

    // Hapus data lama dan insert data baru untuk perangkat
    $delete_perangkat = $koneksi->prepare("DELETE FROM perangkat WHERE id_mr = ?");
    $delete_perangkat->bind_param("i", $id_mr);
    $delete_perangkat->execute();
    $delete_perangkat->close();

    foreach ($perangkat as $p) {
      if (!empty(trim($p)) && $p != 'undefined' && $p != 'null') {
        $insert_perangkat = $koneksi->prepare("INSERT INTO perangkat (id_unit, nama_perangkat, id_mr) VALUES (?, ?, ?)");
$insert_perangkat->bind_param("isi", $id_unit, $p, $id_mr);
        if (!$insert_perangkat->execute()) {
          throw new Exception("Gagal menyimpan perangkat: " . $insert_perangkat->error);
        }
        $insert_perangkat->close();
      }
    }

    // Commit transaction
    $koneksi->commit();

    header("Location: manajemen-laporan.php?success=Laporan berhasil diupdate");
    exit;
  } catch (Exception $e) {
    // Rollback transaction jika ada error
    $koneksi->rollback();
    $error = "Update gagal: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/1.png" />
  <title>Edit Laporan | Sistem MR UPT Komputer</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* untuk menganimasikan navigasi mobile dengan lancar */
    .slide-enter {
      transform: translateY(-8px);
      opacity: 0;
    }

    .slide-enter-active {
      transform: translateY(0);
      opacity: 1;
      transition: all 220ms ease;
    }

    .slide-leave {
      transform: translateY(0);
      opacity: 1;
    }

    .slide-leave-active {
      transform: translateY(-8px);
      opacity: 0;
      transition: all 180ms ease;
    }

    /* memastikan tabel terbungkus dengan baik di layar kecil */
    .table-truncate {
      max-width: 10rem;
    }
  </style>
</head>

<body class="bg-gray-50 font-sans min-h-screen">
  <!-- Navbar -->
  <nav class="bg-blue-600 text-white shadow-lg fixed top-0 left-0 w-full z-50">
    <div class="container mx-auto px-4 sm:px-6">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center gap-3">
          <img src="assets/logo.png" alt="Logo" class="w-9 h-9 object-contain">
          <span class="font-bold text-lg md:text-xl">UPT Komputer UNIPMA</span>
        </div>

        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center space-x-2">
          <a href="dashboard.php" class="hover:bg-blue-700 rounded-lg font-medium px-4 py-2 transition-colors duration-200 flex items-center">
             Beranda
          </a>

          <!-- Dropdown Laporan Desktop -->
          <div class="relative">
            <button id="desktopLaporanBtn" class="hover:bg-blue-700 rounded-lg font-medium px-4 py-2 transition-colors duration-200 flex items-center gap-2 ">
              Laporan
              <i class="fas fa-chevron-down mt-1 text-xs"></i>
            </button>
            <div id="desktopLaporanMenu" class="hidden absolute mt-4 w-56 bg-white text-blue-600 rounded-md shadow-lg overflow-hidden ring-1 ring-black ring-opacity-5">
              <?php if ($level == 'user'): ?>
                <a href="input-laporan-user.php" class="block px-4 py-2 hover:bg-gray-100"> <i class="fas fa-plus mr-2"></i> Input Laporan</a>
              <?php endif; ?>
              <?php if ($level == 'admin'): ?>
                <a href="input-laporan.php" class="block px-4 py-2 hover:bg-gray-100"> <i class="fas fa-plus mr-2"></i> Input Laporan</a>
              <?php endif; ?>
              <?php if ($level == 'user'): ?>
              <a href="laporan.php" class="block px-4 py-2 hover:bg-gray-100"> <i class="fas fa-file-alt mr-2"></i> Data Laporan</a>
              <?php endif; ?>
              <?php if ($level == 'admin'): ?>
                <a href="manajemen-laporan.php" class="block px-4 py-2 hover:bg-gray-100"> <i class="fas fa-tasks mr-2"></i> Manajemen Laporan</a>
              <?php endif; ?>
            </div>
          </div>

          <?php if ($level == 'admin'): ?>
            <a href="petugas.php" class="hover:bg-blue-700 rounded-lg px-4 py-2 font-medium transition-colors duration-200 flex items-center">
              Petugas
            </a>
            <a href="kelola-user.php" class="hover:bg-blue-700 rounded-lg px-4 py-2 font-medium transition-colors duration-200 flex items-center">
              Kelola User
            </a>
          <?php endif; ?>

          <button id="desktopLogoutBtn" class="bg-red-500 hover:bg-red-600 rounded-lg font-medium px-4 py-2 transition-colors duration-200 flex items-center">
            Logout
          </button>
        </div>

        <!-- Mobile: Hamburger -->
        <div class="md:hidden flex items-center">
          <button id="mobileMenuBtn" aria-label="Open menu" class="p-1 rounded-md focus:outline-none focus:ring-2 focus:ring-white hover:bg-blue-700">
            <svg id="hamburgerIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            <svg id="closeIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Menu Mobile (sembunyikan penuh di mobile: semua item ada di dalam) -->
    <div id="mobileMenu" class="md:hidden bg-blue-600 text-white w-full hidden">
      <div class="px-4 pt-4 pb-6 space-y-1">
        <a href="dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700"> Beranda</a>

        <!-- Laporan dapat diperluas di mobile -->
        <div>
          <button id="mobileLaporanToggle" class="w-full text-left px-3 py-2 flex items-center justify-between rounded-md hover:bg-blue-700">
            <span>Laporan</span>
            <i id="mobileLaporanChevron" class="fas fa-chevron-down text-sm"></i>
          </button>
          <div id="mobileLaporanList" class="hidden pl-4 mt-1 space-y-1">
            <?php if ($level == 'user'): ?>
              <a href="input-laporan-user.php" class="block px-3 py-2 rounded-md font-medium hover:bg-blue-700"> <i class="fas fa-plus mr-2"></i> Input Laporan</a>
            <?php endif; ?>
            <?php if ($level == 'admin'): ?>
              <a href="input-laporan.php" class="block px-3 py-2 rounded-md font-medium hover:bg-blue-700"> <i class="fas fa-plus mr-2"></i> Input Laporan</a>
            <?php endif; ?>
            <?php if ($level == 'user'): ?>
            <a href="laporan.php" class="block px-3 py-2 rounded-md font-medium hover:bg-blue-700"> <i class="fas fa-file-alt mr-2"></i> Data Laporan</a>
            <?php endif; ?>
            <?php if ($level == 'admin'): ?>
            <a href="manajemen-laporan.php" class="block px-3 py-2 rounded-md font-medium hover:bg-blue-700"> <i class="fas fa-tasks mr-2"></i> Manajemen Laporan</a>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($level == 'admin'): ?>
          <a href="petugas.php" class="block px-3 py-2 rounded-md font-medium hover:bg-blue-700"> Petugas</a>
          <a href="kelola-user.php" class="block px-3 py-2 rounded-md font-medium hover:bg-blue-700"> Kelola User</a>
        <?php endif; ?>

        <a id="mobileLogout" href="#" class="block px-3 py-2 mt-2 rounded-md font-medium bg-red-500 hover:bg-red-600 text-center"> Logout</a>
      </div>
    </div>
  </nav>

  <!-- Modal Logout -->
  <div id="logoutModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md mx-4">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Konfirmasi Logout</h3>
        <p class="text-gray-600">Apakah Anda yakin ingin keluar dari sistem?</p>
      </div>
      <div class="flex justify-center gap-4">
        <a id="confirmLogout" href="backend/logout.php"
          class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200">
          <i class="fas fa-sign-out-alt mr-2"></i>Keluar
        </a>
        <button onclick="closeModal()"
          class="px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg font-medium transition-colors duration-200">
          <i class="fas fa-times mr-2"></i>Batal
        </button>
      </div>
    </div>
  </div>

  <script>
    // Fungsi modal logout
    function openModal() {
      const modal = document.getElementById('logoutModal');
      modal.classList.remove('hidden');
      document.addEventListener("keydown", handleKeys);
    }

    function closeModal() {
      document.getElementById('logoutModal').classList.add('hidden');
      document.removeEventListener("keydown", handleKeys);
    }

    function handleKeys(e) {
      if (e.key === "Enter") {
        document.getElementById("confirmLogout").click();
      } else if (e.key === "Escape") {
        closeModal();
      }
    }

    // Fungsi Dropdown
    const toggle = document.getElementById('dropdownToggle');
    const menu = document.getElementById('dropdownMenu');

    toggle.addEventListener('click', function() {
      menu.classList.toggle('hidden');
    });

    // Klik di luar dropdown untuk menutup
    document.addEventListener('click', function(e) {
      if (!toggle.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.add('hidden');
      }
    });
  </script>

  <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow mt-8">
    <h1 class="text-2xl font-bold mb-6">Edit Laporan MR: <?php echo $laporan['nomor_mr']; ?></h1>

    <?php if (isset($error)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form id="mrForm" class="space-y-6" enctype="multipart/form-data" action="" method="POST">
      <!-- Form fields sama seperti input-laporan.php, tapi dengan nilai yang sudah ada -->
      <!-- 2. Nomor Surat -->
      <div>
        <label class="block font-semibold mb-1" for="nomorSurat">Nomor Surat</label>
        <input type="text" id="nomorSurat" name="nomorSurat" value="<?php echo htmlspecialchars($laporan['nomor_surat']); ?>" required
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <!-- 3. Keperluan Surat -->
      <div>
        <label class="block font-semibold mb-1" for="keperluanSurat">Keperluan Surat</label>
        <input type="text" id="keperluanSurat" name="keperluanSurat" value="<?php echo htmlspecialchars($laporan['keperluan_surat']); ?>" required
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <!-- 5. Tanggal Laporan -->
      <div>
        <label class="block font-semibold mb-1" for="tanggalLaporan">Tanggal Laporan</label>
        <input type="date" id="tanggalLaporan" name="tanggalLaporan" value="<?php echo htmlspecialchars($laporan['tanggal_laporan']); ?>" required
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <!-- 6. Tanggal Pengecekan -->
      <div>
        <label class="block font-semibold mb-1" for="tanggalPengecekan">Tanggal Pengecekan</label>
        <input type="date" id="tanggalPengecekan" name="tanggalPengecekan" value="<?php echo htmlspecialchars($laporan['tanggal_pengecekan']); ?>" required
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <!-- 7. Perangkat (multi input) -->
      <div>
        <label class="block font-semibold mb-1">Perangkat</label>
        <div id="perangkatList" class="space-y-2">
          <?php foreach ($perangkats as $index => $p): ?>
            <input type="text" name="perangkat[]" value="<?php echo htmlspecialchars($p); ?>" required
              class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Masukkan perangkat" />
          <?php endforeach; ?>
        </div>
        <button type="button" id="addPerangkat" class="mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
          + Tambah Perangkat
        </button>
      </div>

      <!-- 8. Keluhan (multi input) -->
      <div>
        <label class="block font-semibold mb-1">Keluhan</label>
        <div id="keluhanList" class="space-y-2">
          <?php foreach ($keluhans as $index => $k): ?>
            <input type="text" name="keluhan[]" value="<?php echo htmlspecialchars($k); ?>" required
              class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Masukkan keluhan" />
          <?php endforeach; ?>
        </div>
        <button type="button" id="addKeluhan" class="mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
          + Tambah Keluhan
        </button>
      </div>

      <!-- 9. Hasil Pengecekan (multi input) -->
      <div>
        <label class="block font-semibold mb-1">Hasil Pengecekan</label>
        <div id="hasilList" class="space-y-2">
          <?php
          $hasilArray = explode(", ", $laporan['hasil_pengecekan']);
          foreach ($hasilArray as $index => $h):
          ?>
            <input type="text" name="hasilPengecekan[]" value="<?php echo htmlspecialchars($h); ?>" required
              class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Masukkan hasil pengecekan" />
          <?php endforeach; ?>
        </div>
        <button type="button" id="addHasil" class="mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
          + Tambah Hasil Pengecekan
        </button>
      </div>

      <!-- 10. Unit/Biro/Lembaga/Fakultas/Prodi (drop-down pilihan) -->
      <div>
        <label class="block font-semibold mb-1" for="unit">Unit/Biro/Lembaga/Fakultas/Prodi</label>
        <select id="unit" name="id_unit" class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="" disabled>Pilih unit</option>
          <?php
          $unit = mysqli_query($koneksi, "SELECT * FROM unit");
          while ($b = mysqli_fetch_array($unit)) {
            $selected = $b['id_unit'] == $laporan['id_unit'] ? 'selected' : '';
            echo "<option value='{$b['id_unit']}' $selected>{$b['nama_unit']}</option>";
          }
          ?>
        </select>
      </div>

      <!-- 12. Perbaikan (multi input) -->
      <div>
        <label class="block font-semibold mb-1">Perbaikan</label>
        <div id="perbaikanList" class="space-y-2">
          <?php foreach ($perbaikans as $index => $p): ?>
            <input type="text" name="perbaikan[]" value="<?php echo htmlspecialchars($p); ?>" required
              class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Masukkan perbaikan" />
          <?php endforeach; ?>
        </div>
        <button type="button" id="addPerbaikan" class="mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
          + Tambah Perbaikan
        </button>
      </div>

      <!-- 13. Petugas (drop-down daftar teknisi UPT Komputer) -->
      <div>
        <label class="block font-semibold mb-1" for="petugas">Petugas</label>
        <select id="petugas" name="id_petugas" required
          class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="" disabled>Pilih petugas</option>
          <?php
          $petugas = mysqli_query($koneksi, "SELECT * FROM petugas");
          while ($p = mysqli_fetch_array($petugas)) {
            $selected = $p['id_petugas'] == $id_petugas ? 'selected' : '';
            echo "<option value='{$p['id_petugas']}' $selected>{$p['nama_petugas']}</option>";
          }
          ?>
        </select>
      </div>

      <!-- 14. Status -->
      <div>
  <label class="block font-semibold mb-1" for="status">Status</label>
  <select id="status" name="status" required
    class="w-full border border-gray-300 rounded px-3 py-2">
    <option value="Antrian" <?php echo $laporan['status'] == 'Antrian' ? 'selected' : ''; ?>>Antrian</option>
    <option value="Diproses" <?php echo $laporan['status'] == 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
    <option value="Tertunda" <?php echo $laporan['status'] == 'Tertunda' ? 'selected' : ''; ?>>Tertunda</option>
    <option value="Selesai" <?php echo $laporan['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
    <option value="Proses Dibeli Perangkat oleh BAU" <?php echo $laporan['status'] == 'Proses Dibeli Perangkat oleh BAU' ? 'selected' : ''; ?>>Proses Dibeli Perangkat oleh BAU</option>
    <option value="Perangkat Sudah Dibeli oleh BAU" <?php echo $laporan['status'] == 'Perangkat Sudah Dibeli oleh BAU' ? 'selected' : ''; ?>>Perangkat Sudah Dibeli oleh BAU</option>
    <option value="Proses Dibeli Perangkat oleh UPT" <?php echo $laporan['status'] == 'Proses Dibeli Perangkat oleh UPT' ? 'selected' : ''; ?>>Proses Dibeli Perangkat oleh UPT</option>
    <option value="Perangkat Sudah Dibeli oleh UPT" <?php echo $laporan['status'] == 'Perangkat Sudah Dibeli oleh UPT' ? 'selected' : ''; ?>>Perangkat Sudah Dibeli oleh UPT</option>
  </select>
</div>


      <div class="pt-4 flex justify-between">
        <a href="manajemen-laporan.php" class="bg-gray-500 hover:bg-gray-700 text-white px-6 py-2 rounded">
          Batal
        </a>
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
          Update Data
        </button>
      </div>
    </form>
  </div>

  <script>
    // Fungsi untuk menambah input multi
    function addInput(buttonId, containerId, name, placeholder) {
      const btn = document.getElementById(buttonId);
      const container = document.getElementById(containerId);
      btn.addEventListener('click', () => {
        const input = document.createElement('input');
        input.type = 'text';
        input.name = name + '[]';
        input.placeholder = placeholder;
        input.required = true;
        input.className = 'w-full border border-gray-300 rounded px-3 py-2';
        container.appendChild(input);
      });
    }
    addInput('addPerangkat', 'perangkatList', 'perangkat', 'Masukkan perangkat');
    addInput('addKeluhan', 'keluhanList', 'keluhan', 'Masukkan keluhan');
    addInput('addHasil', 'hasilList', 'hasilPengecekan', 'Masukkan hasil pengecekan');
    addInput('addPerbaikan', 'perbaikanList', 'perbaikan', 'Masukkan perbaikan');


    // Mobile menu toggle (hamburger)
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const hamburgerIcon = document.getElementById('hamburgerIcon');
    const closeIcon = document.getElementById('closeIcon');

    mobileMenuBtn.addEventListener('click', () => {
      const isHidden = mobileMenu.classList.contains('hidden');
      if (isHidden) {
        mobileMenu.classList.remove('hidden');
        hamburgerIcon.classList.add('hidden');
        closeIcon.classList.remove('hidden');
      } else {
        mobileMenu.classList.add('hidden');
        hamburgerIcon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
      }
    });

    // Mobile laporan expand
    const mobileLaporanToggle = document.getElementById('mobileLaporanToggle');
    const mobileLaporanList = document.getElementById('mobileLaporanList');
    const mobileLaporanChevron = document.getElementById('mobileLaporanChevron');
    mobileLaporanToggle.addEventListener('click', () => {
      mobileLaporanList.classList.toggle('hidden');
      mobileLaporanChevron.classList.toggle('fa-chevron-down');
      mobileLaporanChevron.classList.toggle('fa-chevron-up');
    });

    // Desktop laporan dropdown
    const desktopLaporanBtn = document.getElementById('desktopLaporanBtn');
    const desktopLaporanMenu = document.getElementById('desktopLaporanMenu');
    desktopLaporanBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      desktopLaporanMenu.classList.toggle('hidden');
    });

    // Click outside to close desktop menu
    document.addEventListener('click', (e) => {
      if (!desktopLaporanMenu.classList.contains('hidden')) {
        if (!desktopLaporanMenu.contains(e.target) && !desktopLaporanBtn.contains(e.target)) {
          desktopLaporanMenu.classList.add('hidden');
        }
      }
    });

    // Logout modal handlers
    const openLogoutBtns = [document.getElementById('desktopLogoutBtn'), document.getElementById('mobileLogout')];
    const logoutModal = document.getElementById('logoutModal');
    const confirmLogout = document.getElementById('confirmLogout');

    openLogoutBtns.forEach(btn => {
      if (!btn) return;
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        logoutModal.classList.remove('hidden');
      });
    });

    function closeModal() {
      logoutModal.classList.add('hidden');
    }

    // Accessibility: close modal on Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (!logoutModal.classList.contains('hidden')) closeModal();
        // close mobile menu as well
        if (!mobileMenu.classList.contains('hidden')) {
          mobileMenu.classList.add('hidden');
          hamburgerIcon.classList.remove('hidden');
          closeIcon.classList.add('hidden');
        }
      }
    });

    // Close mobile menu when resizing to desktop
    window.addEventListener('resize', () => {
      if (window.innerWidth >= 768) {
        if (!mobileMenu.classList.contains('hidden')) {
          mobileMenu.classList.add('hidden');
          hamburgerIcon.classList.remove('hidden');
          closeIcon.classList.add('hidden');
        }
      }
    });
  </script>
</body>

</html>