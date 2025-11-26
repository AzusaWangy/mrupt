<?php
session_start();
include 'backend/koneksi.php';

if (!isset($_SESSION['user'])) {
  header("Location: formlogin.php?pesan=belum_login");
  exit;
}
if ($_SESSION['user']['Level'] != 'admin') {
  // Jika bukan admin, redirect ke halaman lain atau tampilkan error
  header("Location: unauthorized.php");
  exit;
}

$nama  = $_SESSION['user']['nama_user'];
$level = $_SESSION['user']['Level'];

// Ambil semua user kecuali admin
$sql = "SELECT * FROM login WHERE Level != 'admin' ORDER BY status ASC";
$result = mysqli_query($koneksi, $sql);

// Logika untuk menentukan URL tujuan berdasarkan session
$url_lihat = 'formlogin.php';
if (isset($_SESSION['user']['Level'])) {
  if ($_SESSION['user']['Level'] == 'admin') {
    $url_lihat = 'manajemen-laporan.php';
  } elseif ($_SESSION['user']['Level'] == 'user') {
    $url_lihat = 'laporan.php';
  }
}
?>
<!doctype html>
<html class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/1.png" />
  <title>Kelola Users | Sistem MR UPT Komputer</title>
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

<body class="bg-gray-50 font-sans flex flex-col min-h-screen">
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

    // Klik di luar dropdown untuk menutup
    document.addEventListener('click', function(e) {
      if (!toggle.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.add('hidden');
      }
    });
  </script>

  <!-- Main Content -->
  <main class="container flex-grow mx-auto px-4 py-6 pt-20">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h1 class="text-3xl font-bold text-gray-800 mb-1">Daftar User</h1>
      <p class="text-gray-600">Daftar seluruh user yang terdaftar dalam sistem.</p>

      <!-- Tabel -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php
              include 'backend/koneksi.php';
              $query = "SELECT UserID, Username, nama_user, status 
          FROM login 
          WHERE Level != 'admin'";
              $result = mysqli_query($koneksi, $query);

              if (mysqli_num_rows($result) > 0):
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)):
              ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $no++; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['Username']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['nama_user']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      <?php if ($row['status'] == 'aktif'): ?>
                        <span class="text-green-600 font-semibold">Aktif</span>
                      <?php else: ?>
                        <span class="text-red-600 font-semibold">Tidak Aktif</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-4">
                      <?php if ($row['status'] != 'aktif'): ?>
                        <button onclick="aktifkanUser('<?= $row['UserID']; ?>')" class="text-green-600 hover:text-green-900">
                          <i class="fas fa-check mr-1"></i>Aktifkan
                        </button>
                      <?php endif; ?>

                      <?php if ($row['status'] == 'aktif'): ?>
                        <button onclick="nonaktifkanUser('<?= $row['UserID']; ?>')" class="text-yellow-600 hover:text-yellow-900">
                          <i class="fas fa-ban mr-1"></i>Nonaktifkan
                        </button>
                      <?php endif; ?>

                      <button onclick="hapusUser('<?= $row['UserID']; ?>')" class="text-red-600 hover:text-red-900">
                        <i class="fas fa-trash mr-1"></i>Hapus
                      </button>
                    </td>
                  </tr>
                <?php
                endwhile;
              else:
                ?>
                <tr>
                  <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                    Tidak ada data user.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal Konfirmasi Hapus -->
  <div id="modalDelete" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden z-[9999]">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full">
        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-900 mt-4 text-center">Konfirmasi Hapus</h3>
      <p class="text-sm text-gray-600 mt-2 text-center">Apakah Anda yakin ingin menghapus user ini?</p>
      <div class="mt-6 flex justify-center space-x-4">
        <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
          Batal
        </button>
        <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
          Hapus
        </a>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Aktifkan -->
  <div id="modalAktif" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden z-[9999]">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full">
        <i class="fas fa-check text-green-600 text-2xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-900 mt-4 text-center">Aktifkan User</h3>
      <p class="text-sm text-gray-600 mt-2 text-center">User ini akan dapat login ke sistem.</p>
      <div class="mt-6 flex justify-center space-x-4">
        <button onclick="closeAktifModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
          Batal
        </button>
        <a id="aktifLink" href="#" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
          Aktifkan
        </a>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Nonaktifkan -->
  <div id="modalNonaktif" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden z-[9999]">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-center w-16 h-16 mx-auto bg-yellow-100 rounded-full">
        <i class="fas fa-ban text-yellow-600 text-2xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-900 mt-4 text-center">Nonaktifkan User</h3>
      <p class="text-sm text-gray-600 mt-2 text-center">User ini tidak akan dapat login sementara.</p>
      <div class="mt-6 flex justify-center space-x-4">
        <button onclick="closeNonaktifModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
          Batal
        </button>
        <a id="nonaktifLink" href="#" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
          Nonaktifkan
        </a>
      </div>
    </div>
  </div>


  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 mt-16">
    <div class="container mx-auto p-3">
      <div class="text-gray-600 text-sm text-center">
        &copy; 2025 UPT Komputer UNIPMA - Tugas Kelompok RPL
      </div>

    </div>
  </footer>

  <script>
    // Modal Hapus
    function hapusUser(id) {
      document.getElementById('deleteLink').href = "backend/hapus-user.php?id=" + id;
      document.getElementById('modalDelete').classList.remove('hidden');
    }

    function closeDeleteModal() {
      document.getElementById('modalDelete').classList.add('hidden');
    }

    // Modal Aktifkan
    function aktifkanUser(id) {
      document.getElementById('aktifLink').href = "backend/aktifkan-user.php?id=" + id;
      document.getElementById('modalAktif').classList.remove('hidden');
    }

    function closeAktifModal() {
      document.getElementById('modalAktif').classList.add('hidden');
    }

    // Modal Nonaktifkan
    function nonaktifkanUser(id) {
      document.getElementById('nonaktifLink').href = "backend/nonaktif-user.php?id=" + id;
      document.getElementById('modalNonaktif').classList.remove('hidden');
    }

    function closeNonaktifModal() {
      document.getElementById('modalNonaktif').classList.add('hidden');
    }


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