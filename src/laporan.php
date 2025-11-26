<?php
// Koneksi ke database dan session start
include 'backend/koneksi.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: formlogin.php");
    exit;
}

// Ambil data user dari session
$nama = $_SESSION['user']['nama_user'];
$level = $_SESSION['user']['Level'];
$userid = $_SESSION['user']['UserID'];

// Query untuk mengambil data laporan dari database
$query = "SELECT 
            lm.*, 
            u.nama_unit AS nama_unit_laporan,
            GROUP_CONCAT(DISTINCT p.nama_perangkat SEPARATOR ', ') AS perangkat,
            GROUP_CONCAT(DISTINCT k.deskripsi_keluhan SEPARATOR ', ') AS keluhan,
            GROUP_CONCAT(DISTINCT pb.deskripsi_perbaikan SEPARATOR ', ') AS perbaikan,
            GROUP_CONCAT(DISTINCT pt.nama_petugas SEPARATOR ', ') AS nama_petugas
          FROM laporan_mr lm 
          LEFT JOIN unit u ON lm.id_unit = u.id_unit 
          LEFT JOIN perangkat p ON lm.id_mr = p.id_mr 
          LEFT JOIN keluhan k ON lm.id_mr = k.id_mr
          LEFT JOIN perbaikan pb ON lm.id_mr = pb.id_mr
          LEFT JOIN petugas pt ON pb.id_petugas = pt.id_petugas
          GROUP BY lm.id_mr
          ORDER BY lm.tanggal_laporan DESC";

$result = mysqli_query($koneksi, $query);

// Handle filter jika ada
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_unit = isset($_GET['unit']) ? $_GET['unit'] : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

if ($filter_status || $filter_unit || $search) {
    $query = "SELECT 
                lm.*, 
                u.nama_unit as nama_unit_laporan,
                GROUP_CONCAT(DISTINCT p.nama_perangkat SEPARATOR ', ') as perangkat,
                GROUP_CONCAT(DISTINCT k.deskripsi_keluhan SEPARATOR ', ') as keluhan,
                GROUP_CONCAT(DISTINCT pb.deskripsi_perbaikan SEPARATOR ', ') AS perbaikan,
                pt.nama_petugas
              FROM laporan_mr lm 
              LEFT JOIN unit u ON lm.id_unit = u.id_unit 
              LEFT JOIN perangkat p ON lm.id_mr = p.id_mr
              LEFT JOIN keluhan k ON lm.id_mr = k.id_mr
              LEFT JOIN perbaikan pb ON lm.id_mr = pb.id_mr
              LEFT JOIN petugas pt ON pb.id_petugas = pt.id_petugas
              WHERE 1=1";


    if ($filter_status) {
        $query .= " AND lm.status = '$filter_status'";
    }

    if ($filter_unit) {
        $query .= " AND lm.id_unit = $filter_unit";
    }
    
    if ($search) {
        $query .= " AND (
            lm.nomor_mr LIKE '%$search%' OR
            lm.keperluan_surat LIKE '%$search%' OR
            u.nama_unit LIKE '%$search%' OR
            p.nama_perangkat LIKE '%$search%' OR
            k.deskripsi_keluhan LIKE '%$search%' OR
            pb.deskripsi_perbaikan LIKE '%$search%' OR
            pt.nama_petugas LIKE '%$search%'
        )";
    }

    $query .= " GROUP BY lm.id_mr ORDER BY lm.tanggal_laporan DESC";
    $result = mysqli_query($koneksi, $query);
}

// Query untuk dropdown unit
$unit_query = "SELECT * FROM unit";
$unit_result = mysqli_query($koneksi, $unit_query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Laporan | Sistem MR UPT Komputer</title>
    <link rel="icon" type="image/png" href="assets/1.png" />
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

<body class="bg-gray-100">
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
        <div class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-4">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Konfirmasi Logout</h3>
                <p class="text-gray-600">Apakah Anda yakin ingin keluar dari sistem?</p>
            </div>
            <div class="flex justify-center gap-4">
                <a id="confirmLogout" href="backend/logout.php" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">Keluar</a>
                <button onclick="closeModal()" class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg">Batal</button>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="container mx-auto px-4 py-6 pt-20">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Data Laporan MR</h1>
            <p class="text-gray-600">Lihat & pantau semua laporan Maintenance dan Repair perangkat komputer</p>

            <!-- Filter dan Pencarian -->
<div class="bg-white rounded-lg shadow-sm p-4 mt-12">
  <form method="GET" action="" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

    <!-- Filter dan Tombol -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Filter Status -->
      <div class="relative">
        <select name="status"
          class="block w-44 md:w-48 px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
          <option value="">Semua Status</option>
          <option value="Antrian" <?= $filter_status == 'Antrian' ? 'selected' : '' ?>>Antrian</option>
          <option value="Diproses" <?= $filter_status == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
          <option value="Tertunda" <?= $filter_status == 'Tertunda' ? 'selected' : '' ?>>Tertunda</option>
          <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
          <option value="Proses Dibeli Perangkat oleh BAU" <?= $filter_status == 'Proses Dibeli Perangkat oleh BAU' ? 'selected' : '' ?>>Proses Dibeli oleh BAU</option>
          <option value="Perangkat Sudah Dibeli oleh BAU" <?= $filter_status == 'Perangkat Sudah Dibeli oleh BAU' ? 'selected' : '' ?>>Sudah Dibeli oleh BAU</option>
          <option value="Proses Dibeli Perangkat oleh UPT" <?= $filter_status == 'Proses Dibeli Perangkat oleh UPT' ? 'selected' : '' ?>>Proses Dibeli oleh UPT</option>
          <option value="Perangkat Sudah Dibeli oleh UPT" <?= $filter_status == 'Perangkat Sudah Dibeli oleh UPT' ? 'selected' : '' ?>>Sudah Dibeli oleh UPT</option>
        </select>
      </div>

      <!-- Filter Unit -->
      <div class="relative">
        <select name="unit"
          class="block w-44 md:w-48 px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
          <option value="">Semua Unit</option>
          <?php mysqli_data_seek($unit_result, 0); while ($unit = mysqli_fetch_assoc($unit_result)): ?>
            <option value="<?= $unit['id_unit'] ?>" <?= $filter_unit == $unit['id_unit'] ? 'selected' : '' ?>>
              <?= $unit['nama_unit'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Tombol Filter -->
      <button type="submit"
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm transition-all duration-200">
        <i class="fas fa-filter"></i> Filter
      </button>

      <!-- Tombol Reset -->
      <a href="laporan.php"
        class="flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm transition-all duration-200">
        <i class="fas fa-sync-alt"></i> Reset
      </a>
    </div>

    <!-- Search -->
    <div class="relative w-full md:w-64 md:ml-auto">
      <input type="text" name="search"
        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
        placeholder="Cari laporan..."
        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
      <button type="submit"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
        <i class="fas fa-search"></i>
      </button>
    </div>

  </form>
</div>


        </div>

        <!-- Tabel Laporan -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">No. MR</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Keperluan</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Unit</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Perangkat</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Perbaikan</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Petugas</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white text-xs divide-y divide-gray-200">
                        <!-- Tr dan td isi konten tetap sama, hanya kurangi padding -->
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-4 py-2"><?= $row['nomor_mr']; ?><br><span class="text-gray-500 text-[10px]">No. Surat: <?= $row['nomor_surat'] ? $row['nomor_surat'] : '<span class="text-gray-400">Tidak Ada</span>'; ?></span></td>
                  <td class="px-4 py-2"><?= $row['keperluan_surat'] ? $row['keperluan_surat'] : '<span class="text-gray-400">Tidak Ada</span>'; ?></td>
                                    <td class="px-4 py-2"><?= date('d M Y', strtotime($row['tanggal_laporan'])); ?><br><span class="text-gray-500 text-[10px]">Pengecekan: <?= date('d M Y', strtotime($row['tanggal_pengecekan'])); ?></span></td>
                                    <td class="px-4 py-2"><?= $row['nama_unit_laporan']; ?></td>
                                    <td class="px-4 py-2"><?= $row['perangkat']; ?><br><span class="text-gray-500 text-[10px]">Keluhan: <?= $row['keluhan']; ?></span></td>
                                    <td class="px-4 py-2"><?= $row['perbaikan'] ? $row['perbaikan'] : '<span class="text-gray-400">Belum diperbaiki</span>'; ?></td>
                                    <td class="px-4 py-2"><?= $row['nama_petugas'] ? $row['nama_petugas'] : '<span class="text-gray-400">Belum ditugaskan</span>'; ?> </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <?php
                                        $status = trim($row['status']);

                                        switch ($status) {
                                            case 'Antrian':
                                                $status_class = 'bg-yellow-100 text-yellow-700';
                                                $icon = 'fa-clock';
                                                break;

                                            case 'Diproses':
                                                $status_class = 'bg-blue-100 text-blue-700';
                                                $icon = 'fa-tools';
                                                break;

                                            case 'Tertunda':
                                                $status_class = 'bg-orange-100 text-orange-700';
                                                $icon = 'fa-pause-circle';
                                                break;

                                            case 'Proses Dibeli Perangkat oleh BAU':
                                                $status_class = 'bg-purple-100 text-purple-700';
                                                $icon = 'fa-shopping-cart';
                                                break;

                                            case 'Perangkat Sudah Dibeli oleh BAU':
                                                $status_class = 'bg-indigo-100 text-indigo-700';
                                                $icon = 'fa-box';
                                                break;

                                            case 'Proses Dibeli Perangkat oleh UPT':
                                                $status_class = 'bg-pink-100 text-pink-700';
                                                $icon = 'fa-shopping-cart';
                                                break;

                                            case 'Perangkat Sudah Dibeli oleh UPT':
                                                $status_class = 'bg-emerald-300 text-emerald-900';
                                                $icon = 'fa-box';
                                                break;

                                            case 'Selesai':
                                                $status_class = 'bg-green-100 text-green-700';
                                                $icon = 'fa-check-circle';
                                                break;

                                            default:
                                                $status_class = 'bg-gray-100 text-gray-700';
                                                $icon = 'fa-question-circle';
                                                break;
                                        }
                                        ?>
                                        <span class="inline-flex items-center px-2 py-[2px] text-[11px] font-medium rounded-full <?= $status_class ?>">
                                            <i class="fas <?= $icon ?> mr-1 text-[11px]"></i>
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    <td class="px-4 py-2">
  <div class="flex items-center justify-center">
    <a href="detail-laporan.php?id=<?= $row['id_mr']; ?>"
      class="text-blue-600 hover:text-blue-900 text-xs flex items-center gap-1">
      <i class="fas fa-eye text-[12px]"></i>
      Detail
    </a>
  </div>
</td>


                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data laporan yang ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>

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