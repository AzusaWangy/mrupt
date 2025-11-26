<?php
include 'backend/koneksi.php';
session_start();

if (!isset($_SESSION['user'])) {
  header("Location: formlogin.php");
  exit;
}

$nama  = $_SESSION['user']['nama_user'];
$level = $_SESSION['user']['Level'];
$userid  = $_SESSION['user']['UserID'];

// Ambil daftar petugas dari database
$query_petugas = "SELECT * FROM petugas ORDER BY nama_petugas ASC";
$result_petugas = mysqli_query($koneksi, $query_petugas);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/1.png" />
  <title>Input Laporan | Sistem MR UPT Komputer</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 font-sans min-h-screen" >
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

  <br><br><br>
  <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Form Input Laporan Kerusakan</h1>
    <form id="mrForm" class="space-y-6" enctype="multipart/form-data" action="./backend/proses-laporan-user.php" method="POST">

      <!-- 3. Lampiran (upload dokumen opsional) -->
      <div>
        <label class="block font-semibold mb-1" for="lampiran">Lampiran (opsional)</label>
        <input type="file" id="lampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
          class="w-full" />
        <p class="text-sm text-gray-500 mt-1">Upload surat resmi, bukti foto, dll.</p>
      </div>

      <!-- 4. Tanggal Laporan -->
      <div>
        <label class="block font-semibold mb-1" for="tanggalLaporan">Tanggal Laporan</label>
        <input type="date" id="tanggalLaporan" name="tanggalLaporan" required
          class="w-full border border-gray-300 rounded px-3 py-2" value="<?= date('Y-m-d'); ?>" />
      </div>

      <!-- 5. Perangkat (multi input) -->
      <div>
        <label class="block font-semibold mb-1">Perangkat yang Bermasalah</label>
        <div id="perangkatList" class="space-y-2">
          <input type="text" name="perangkat[]" required
            class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Masukkan perangkat" />
        </div>
        <button type="button" id="addPerangkat" class="mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
          + Tambah Perangkat
        </button>
      </div>

      <!-- 6. Keluhan (multi input) -->
      <div>
        <label class="block font-semibold mb-1">Keluhan / Masalah</label>
        <div id="keluhanList" class="space-y-2">
          <input type="text" name="keluhan[]" required
            class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Masukkan keluhan" />
        </div>
        <button type="button" id="addKeluhan" class="mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
          + Tambah Keluhan
        </button>
      </div>

      <!-- 7. Unit/Biro/Lembaga/Fakultas/Prodi (drop-down pilihan) -->
      <div>
        <label class="block font-semibold mb-1" for="unit">Unit/Biro/Lembaga/Fakultas/Prodi</label>
        <select id="unit" name="id_unit" class="w-full border border-gray-300 rounded px-3 py-2" required>
          <option value="" disabled selected>Pilih unit</option>
          <?php
          $unit = mysqli_query($koneksi, "SELECT * FROM unit");
          while ($b = mysqli_fetch_array($unit)) {
            echo "<option value='{$b['id_unit']}'>{$b['nama_unit']}</option>";
          }
          ?>
          <option value="99">Lainnya</option>
        </select>
        <p class="text-sm font-normal text-gray-700">*Bila unit tidak ada, pilih Lainnya</p>
      </div>

      <!-- 8. Unit/Biro/Lembaga Lain (isian manual jika tidak ada di daftar) -->
      <div id="unitLainContainer" style="display: none;">
        <label class="block font-semibold mb-1" for="unitLain">Unit/Biro/Lembaga Lain</label>
        <input type="text" id="unitLain" name="unitLain"
          class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Isi nama unit baru" />
      </div>

      <!-- HILANGKAN: Hasil Pengecekan, Perbaikan, Petugas, dan Status -->

      <div class="pt-4">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
          Kirim Laporan
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

    // Fungsi show/hide unit lain
    document.addEventListener('DOMContentLoaded', function() {
      const unitSelect = document.getElementById('unit');
      const unitLainContainer = document.getElementById('unitLainContainer');
      
      function toggleUnitLain() {
        if (unitSelect.value === '99') {
          unitLainContainer.style.display = 'block';
          // Tambah required jika unit lain ditampilkan
          document.getElementById('unitLain').required = true;
        } else {
          unitLainContainer.style.display = 'none';
          document.getElementById('unitLain').required = false;
        }
      }
      
      // Initial state
      toggleUnitLain();
      
      // Listen for changes
      unitSelect.addEventListener('change', toggleUnitLain);
    });
      
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
  <br><br><br><br><br><br>
</body>

</html>