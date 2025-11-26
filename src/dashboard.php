<?php
session_start();
include 'backend/koneksi.php';

if (!isset($_SESSION['user'])) {
  header("Location: ../index.php?pesan=belum_login");
  exit;
}

$nama  = $_SESSION['user']['nama_user'];
$level = $_SESSION['user']['Level'];

// Logika untuk menentukan URL tujuan berdasarkan session
$url_lihat = 'formlogin.php';
if (isset($_SESSION['user']['Level'])) {
  if ($_SESSION['user']['Level'] == 'admin') {
    $url_lihat = 'manajemen-laporan.php';
  } elseif ($_SESSION['user']['Level'] == 'user') {
    $url_lihat = 'laporan.php';
  }
}

// Query untuk statistik dashboard.php
$query_antrian = "SELECT COUNT(*) as total FROM laporan_mr WHERE status = 'Antrian'";
$query_diproses = "SELECT COUNT(*) as total FROM laporan_mr WHERE status IN ('Diproses', 'Tertunda', 'Proses Dibeli Perangkat oleh BAU', 'Perangkat Sudah Dibeli oleh BAU', 'Proses Dibeli Perangkat oleh UPT', 'Perangkat Sudah Dibeli oleh UPT')"; 
$query_selesai = "SELECT COUNT(*) as total FROM laporan_mr WHERE status = 'Selesai'";

$result_antrian = mysqli_query($koneksi, $query_antrian);
$result_diproses = mysqli_query($koneksi, $query_diproses);
$result_selesai = mysqli_query($koneksi, $query_selesai);

$total_antrian = mysqli_fetch_assoc($result_antrian)['total'] ?? 0;
$total_diproses = mysqli_fetch_assoc($result_diproses)['total'] ?? 0;
$total_selesai = mysqli_fetch_assoc($result_selesai)['total'] ?? 0;

// Query untuk data laporan terbaru (5 data terbaru)
$query_laporan = "SELECT 
                    lm.*, 
                    u.nama_unit as nama_unit_laporan,
                    GROUP_CONCAT(DISTINCT p.nama_perangkat SEPARATOR ', ') as perangkat
                  FROM laporan_mr lm 
                  LEFT JOIN unit u ON lm.id_unit = u.id_unit 
                  LEFT JOIN perangkat p ON lm.id_mr = p.id_mr
                  GROUP BY lm.id_mr
                  ORDER BY lm.tanggal_laporan DESC 
                  LIMIT 5";

$result_laporan = mysqli_query($koneksi, $query_laporan);
?>
<!doctype html>
<html class="scroll-smooth" lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/1.png" />
  <title>Dashboard | Sistem MR UPT Komputer</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="js/lockdown.js"></script>
<style>
    /* Lockdown specific styles */
    #lockdown-warning {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    /* Hide browser controls when possible */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }



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

        <a id="mobileLogout" href="#" class="block px-3 py-2 mt-2 rounded-md font-medium bg-red-500 hover:bg-red-600 text-center"> 
      <i class="fas fa-sign-out-alt mr-2"></i>Logout
    </a>
      
      </div>
    </div>
  </nav>

  <!-- Ganti modal logout yang sudah ada -->
<div id="logoutModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="text-center mb-4">
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-door-open text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Konfirmasi Logout</h3>
            <p class="text-gray-600 mb-2">Ini adalah SATU-SATUNAYA cara aman untuk keluar dari sistem.</p>
            <p class="text-sm text-green-600 font-medium">
                <i class="fas fa-shield-alt"></i> Sistem dalam Mode Lockdown
            </p>
        </div>
        <div class="flex justify-center gap-4">
            <button onclick="mrUptLockdown.safeLogout()" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout Aman
            </button>
            <button onclick="closeModal()" class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg">
                Batal
            </button>
        </div>
        <?php if ($level == 'admin'): ?>
        <div class="mt-4 text-center">
            <button onclick="mrUptLockdown.emergencyExit()" class="text-xs text-red-600 hover:text-red-700">
                <i class="fas fa-key mr-1"></i>Emergency Exit (Admin Only)
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

  <!-- Main Content -->
  <main class="container mx-auto px-4 pt-20 pb-12">
    <?php if (isset($_SESSION['login_success'])): ?>
      <div id="loginAlert" class="fixed z-[9999] transition-opacity duration-500" style="bottom: 20px; left: 20px;">
        <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg flex items-center gap-2">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          <span class="font-semibold">Login berhasil!</span>
          <span class="text-sm opacity-90">Selamat datang, <?php echo htmlspecialchars($nama); ?>!</span>
        </div>
      </div>
      <script>
        setTimeout(() => {
          const alertBox = document.getElementById('loginAlert');
          if (alertBox) {
            alertBox.classList.add('opacity-0');
            setTimeout(() => alertBox.remove(), 500);
          }
        }, 3000);
      </script>
      <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Sistem MR UPT Komputer</h1>
        <p class="text-gray-600 text-md">Selamat datang di Sistem Maintenance & Repair UPT Komputer</p>
      </div>
      <div class="bg-white rounded-lg shadow-sm px-5 py-3 border border-gray-200">
        <div class="flex items-center gap-3">
          <div class="px-3 py-2 bg-blue-100 rounded-full flex items-center justify-center">
            <i class="fas fa-user text-blue-600 text-xl"></i>
          </div>
          <div>
            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($nama); ?></p>
            <p class="text-sm text-gray-500 capitalize"><?php echo $level; ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Cards Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- Card Menunggu -->
      <div class="bg-white rounded-2xl shadow-sm border-l-4 border-yellow-400 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-4xl font-bold text-gray-900"><?php echo $total_antrian; ?></p>
              <p class="text-gray-600 font-medium text-lg">Antrian</p>
            </div>
            <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center">
              <i class="fas fa-clock text-yellow-600 text-2xl"></i>
            </div>
          </div>
          <p class="text-sm text-gray-500">Laporan dalam antrian</p>
        </div>
      </div>

      <!-- Card Diproses -->
      <div class="bg-white rounded-2xl shadow-sm border-l-4 border-blue-400 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-4xl font-bold text-gray-900"><?php echo $total_diproses; ?></p>
              <p class="text-gray-600 font-medium text-lg">Diproses</p>
            </div>
            <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
              <i class="fas fa-tools text-blue-600 text-2xl"></i>
            </div>
          </div>
          <p class="text-sm text-gray-500">Sedang dalam penanganan</p>
        </div>
      </div>

      <!-- Card Selesai -->
      <div class="bg-white rounded-2xl shadow-sm border-l-4 border-green-400 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-4xl font-bold text-gray-900"><?php echo $total_selesai; ?></p>
              <p class="text-gray-600 font-medium text-lg">Selesai</p>
            </div>
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
              <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
          </div>
          <p class="text-sm text-gray-500">Laporan yang telah selesai</p>
        </div>
      </div>
    </div>
      
              <!-- Tombol Aksi User -->
<?php if ($level == 'user'): ?>
  <div class="mt-12 mb-12 bg-gradient-to-r from-blue-100 via-blue-50 to-indigo-100 py-12 px-6">
    <div class="max-w-3xl mx-auto text-center">
      <h3 class="text-xl font-bold text-gray-800 mb-3">Ingin melaporkan kerusakan perangkat?</h3>
      <p class="text-gray-700 text-sm mb-6">
        Klik tombol di bawah untuk membuat laporan baru dan pantau proses perbaikannya secara real-time.
      </p>
      <a href="input-laporan-user.php" 
         class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition duration-300">
        <i class="fas fa-plus"></i>
        Buat Laporan Baru
      </a>
    </div>
  </div>
<?php endif; ?>





    <!-- Tabel Laporan Terbaru -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Laporan Terbaru</h2>
            <p class="text-gray-600 mt-1">laporan maintenance terbaru</p>
          </div>
          <a href="<?php echo htmlspecialchars($url_lihat); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2">
            <i class="fas fa-list"></i>
            Lihat Semua Laporan
          </a>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">No MR</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tanggal</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Unit</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Perangkat</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if ($result_laporan && mysqli_num_rows($result_laporan) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result_laporan)): ?>
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($row['nomor_mr']); ?></div>
                    <div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($row['nomor_surat']); ?></div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm text-gray-900 font-medium"><?php echo date('d M Y', strtotime($row['tanggal_laporan'])); ?></div>
                    <div class="text-xs text-gray-500 mt-1">Pengecekan: <?php echo date('d M Y', strtotime($row['tanggal_pengecekan'])); ?></div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($row['nama_unit_laporan']); ?></div>
                  </td>
                  <td class="px-4 py-3 max-w-xs table-truncate">
                    <div class="text-sm text-gray-900 truncate"><?php echo htmlspecialchars($row['perangkat']); ?></div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
  <?php
  $status = trim($row['status']);
  $status_class = '';
  $status_icon = '';

  switch ($status) {
    case 'Antrian':
      $status_class = 'bg-yellow-100 text-yellow-800 border-yellow-300';
      $status_icon = 'fa-clock';
      break;

    case 'Diproses':
      $status_class = 'bg-blue-100 text-blue-800 border-blue-300';
      $status_icon = 'fa-tools';
      break;

    case 'Tertunda':
      $status_class = 'bg-orange-100 text-orange-800 border-orange-300';
      $status_icon = 'fa-pause-circle';
      break;

    case 'Selesai':
      $status_class = 'bg-green-100 text-green-800 border-green-300';
      $status_icon = 'fa-check-circle';
      break;

    case 'Proses Dibeli Perangkat oleh BAU':
      $status_class = 'bg-purple-100 text-purple-800 border-purple-300';
      $status_icon = 'fa-shopping-cart';
      break;

    case 'Perangkat Sudah Dibeli oleh BAU':
      $status_class = 'bg-indigo-100 text-indigo-800 border-indigo-300';
      $status_icon = 'fa-box';
      break;

    case 'Proses Dibeli Perangkat oleh UPT':
      $status_class = 'bg-pink-100 text-pink-800 border-pink-300';
      $status_icon = 'fa-shopping-cart';
      break;

    case 'Perangkat Sudah Dibeli oleh UPT':
      $status_class = 'bg-emerald-300 text-emerald-900 border-emerald-500';
      $status_icon = 'fa-box';
      break;

    default:
      $status_class = 'bg-gray-100 text-gray-800 border-gray-300';
      $status_icon = 'fa-question-circle';
      break;
  }
  ?>
  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border <?php echo $status_class; ?>">
    <i class="fas <?php echo $status_icon; ?> mr-2 text-[11px]"></i>
    <?php echo htmlspecialchars($status); ?>
  </span>
</td>

                  <td class="px-4 py-3 whitespace-nowrap text-center">
                    <a href="<?php echo htmlspecialchars($url_lihat); ?>" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200">
                      <i class="fas fa-eye mr-2"></i>
                      Lihat
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="px-4 py-12 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium">Belum ada laporan</p>
                    <p class="text-sm mt-1">Mulai dengan membuat laporan maintenance pertama</p>
                    <?php if ($level == 'admin'): ?>
                      <a href="input-laporan.php" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 inline-flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Buat Laporan Baru
                      </a>
                    <?php endif; ?>
                       <?php if ($level == 'user'): ?>
                      <a href="input-laporan-user.php" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 inline-flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Buat Laporan Baru
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 mt-12">
    <div class="container mx-auto p-3 text-center text-sm text-gray-600">
      &copy; 2025 UPT Komputer UNIPMA - Tugas Kelompok RPL
    </div>
  </footer>

  <script>
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

   // Ganti event listener logout yang sudah ada
const openLogoutBtns = [document.getElementById('desktopLogoutBtn'), document.getElementById('mobileLogout')];

openLogoutBtns.forEach(btn => {
    if (!btn) return;
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        // Show custom lockdown logout modal
        logoutModal.classList.remove('hidden');
    });
});

// ✅ TAMBAHKAN FUNCTION INI
function closeModal() {
  const logoutModal = document.getElementById('logoutModal');
  if (logoutModal) {
    logoutModal.classList.add('hidden');
  }
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