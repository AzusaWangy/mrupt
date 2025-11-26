<?php
session_start();

$nama  = $_SESSION['user']['nama_user'] ?? 'Guest';
$level = $_SESSION['user']['Level'] ?? 'guest';

if (isset($_GET['pesan']) && $_GET['pesan'] == "belum_login") {
  echo '
    <div id="loginAlert" class="fixed top-20 left-5">
  <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-6 py-4 rounded-lg shadow-lg flex items-center gap-2">
    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
    </svg>
    <span class="font-semibold">Anda harus login dulu!</span>
  </div>
</div>

<script>
  setTimeout(() => {
    const alertBox = document.getElementById("loginAlert");
    if (alertBox) {
      alertBox.style.transition = "opacity 0.5s";
      alertBox.style.opacity = "0";
      setTimeout(() => alertBox.remove(), 500);
    }
  }, 3000);
</script>

    ';
}
?>
<!doctype html>
<html class="scroll-smooth" lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="./src/assets/1.png" />
  <title>MR UPT Komputer</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-blue-600 text-white shadow-lg fixed top-0 left-0 w-full z-50">
        <div class="container mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <img src="./src/assets/logo.png" alt="Logo" class="w-9 h-9 object-contain">
                <span class="font-bold text-lg md:text-xl leading-tight">UPT Komputer UNIPMA</span>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-2">
                <a href="#home" class="hover:text-gray-300 rounded-lg px-4 py-2 font-medium transition-colors duration-200 flex items-center">
                    Beranda
                </a>
                <a href="#layanan" class="hover:text-gray-300 rounded-lg px-4 py-2 font-medium transition-colors duration-200 flex items-center">
                    Layanan
                </a>
                <a href="#cara" class="hover:text-gray-300 rounded-lg px-4 py-2 font-medium transition-colors duration-200 flex items-center">
                    Cara Menggunakan
                </a>
                <a href="./src/formlogin.php" class="bg-white text-blue-600 font-medium px-4 py-2 rounded-lg shadow hover:bg-gray-100 transition-colors flex items-center">
                    Login
                </a>
            </div>

            <!-- Mobile Hamburger -->
            <div class="md:hidden flex items-center">
                <button id="mobileMenuBtn" aria-label="Open menu" class="p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-white">
                    <svg id="hamburgerIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg id="closeIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden bg-blue-600 text-white w-full hidden border-t border-blue-500">
            <div class="px-4 pt-4 pb-6 space-y-2">
                <a href="#home" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 flex items-center">
                     Beranda
                </a>
                <a href="#layanan" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 flex items-center">
                    Layanan
                </a>
                <a href="#cara" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 flex items-center">
                    Cara Menggunakan
                </a>
                <a href="./src/formlogin.php"
                    class="block px-3 py-2 rounded-md bg-white text-blue-600 hover:bg-gray-300 font-semibold text-center mt-3 flex justify-center items-center">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 pt-4">
        <!-- Hero Section -->
        <section id="home" class="bg-blue-600 text-white py-20 pb-10 px-4">
            <div class="container mx-auto text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-6 leading-snug text-white">
  					Maintenance & Repair UPT Komputer UNIPMA
				</h1>

                <p class="text-base text-blue-100 sm:text-lg md:text-xl mb-20 max-w-2xl mx-auto leading-relaxed">
                    Sistem berbasis web untuk mencatat laporan kerusakan, melacak status perbaikan, dan mengelola data perawatan perangkat di lingkungan UPT Komputer UNIPMA secara efisien.
                </p>
                <a href="#layanan"
                    class="bg-white text-blue-600 font-semibold px-6 py-3 rounded-lg shadow hover:bg-gray-100 transition-colors">
                    Pelajari Layanan
                </a>
            </div>
        </section>


        <!-- Layanan Section -->
        <section id="layanan" class="py-20 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Layanan MR UPT</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow text-center">
                        <i class="fas fa-plus text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Input Laporan</h3>
                        <p>Pengguna dapat dengan mudah menginput laporan kegiatan atau masalah yang terjadi di lingkungan UPT Komputer.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow text-center">
                        <i class="fas fa-file-alt text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Data Laporan</h3>
                        <p>Semua laporan yang masuk dapat dilihat, dicari, dan difilter dengan cepat untuk memudahkan manajemen data.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow text-center">
                        <i class="fas fa-tasks text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Manajemen Laporan</h3>
                        <p>Admin dapat memproses, mengelola status, dan memonitor seluruh laporan untuk memastikan tugas terselesaikan dengan baik.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cara Menggunakan Section -->
        <section id="cara" class="py-20 bg-white">
  <div class="container mx-auto px-4 text-center">
    <h2 class="text-3xl font-bold mb-4 text-gray-800">Cara Menggunakan MR UPT</h2>
    <p class="text-gray-600 mb-12">
      Proses penggunaan sistem MR UPT yang sederhana dalam 4 langkah mudah
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
      <!-- Langkah 1 -->
      <div class="flex flex-col items-center text-center">
        <div class="w-16 h-16 flex items-center justify-center bg-blue-600 text-white text-2xl font-bold rounded-full mb-4">
          1
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Login ke Sistem</h3>
        <p class="text-gray-600 text-sm">
          Masuk menggunakan akun yang telah terdaftar. Admin dan user memiliki hak akses berbeda.
        </p>
      </div>

      <!-- Langkah 2 -->
      <div class="flex flex-col items-center text-center">
        <div class="w-16 h-16 flex items-center justify-center bg-blue-600 text-white text-2xl font-bold rounded-full mb-4">
          2
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Input Laporan</h3>
        <p class="text-gray-600 text-sm">
          Setelah login, pengguna dapat menambahkan laporan baru melalui menu <b>Input Laporan</b>.
        </p>
      </div>

      <!-- Langkah 3 -->
      <div class="flex flex-col items-center text-center">
        <div class="w-16 h-16 flex items-center justify-center bg-blue-600 text-white text-2xl font-bold rounded-full mb-4">
          3
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Proses Laporan</h3>
        <p class="text-gray-600 text-sm">
          Admin memproses laporan yang masuk dan memperbarui status penanganannya.
        </p>
      </div>

      <!-- Langkah 4 -->
      <div class="flex flex-col items-center text-center">
        <div class="w-16 h-16 flex items-center justify-center bg-blue-600 text-white text-2xl font-bold rounded-full mb-4">
          4
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Pantau Progress</h3>
        <p class="text-gray-600 text-sm">
          Pengguna dapat melacak perkembangan laporan secara real-time hingga selesai.
        </p>
      </div>
    </div>
  </div>
</section>

    </main>

    <!-- Footer -->
<footer class="bg-blue-600 text-white mt-12">
  <div class="container mx-auto px-6 py-10 grid grid-cols-1 md:grid-cols-[1.4fr_1fr_1.3fr] gap-12 text-center md:text-left">
    
    <!-- Kolom 1: Tentang -->
    <div>
      <h2 class="text-2xl font-bold mb-3">Maintenance & Repair UPT</h2>
      <p class="text-sm leading-relaxed text-gray-200">
        Sistem Maintenance & Repair (MR) UPT Komputer UNIPMA membantu dalam pencatatan laporan kerusakan, 
        pelacakan status perbaikan, serta pengelolaan data perawatan perangkat secara efisien dan terpusat.
      </p>
    </div>

    <!-- Kolom 2: Navigasi -->
    <div class="md:pl-8">
      <h2 class="text-xl font-bold mb-3">Navigasi</h2>
      <ul class="space-y-2 text-gray-200">
        <li><a href="#home" class="hover:text-white transition-colors">Beranda</a></li>
        <li><a href="#layanan" class="hover:text-white transition-colors">Layanan</a></li>
        <li><a href="#cara" class="hover:text-white transition-colors">Cara Menggunakan</a></li>
        <li><a href="./src/formlogin.php" class="hover:text-white transition-colors">Login</a></li>
      </ul>
    </div>

    <!-- Kolom 3: Kontak -->
    <div>
      <h2 class="text-xl font-bold mb-3">Hubungi Kami</h2>
      <ul class="text-gray-200 space-y-3">
        <li class="flex items-start justify-center md:justify-start gap-3">
          <i class="fas fa-map-marker-alt mt-1"></i>
          <span class="text-sm leading-relaxed">
            Jl. Setia Budi No.85,
            Kanigoro, Kec. Kartoharjo,<br>
            Kota Madiun, Jawa Timur 63118
          </span>
        </li>
        <li class="flex justify-center md:justify-start items-center gap-3">
          <i class="fas fa-envelope"></i>
          <a href="mailto:uptkomputer@unipma.ac.id" class="hover:text-white text-sm">uptkomputer@unipma.ac.id</a>
        </li>
        <li class="flex justify-center md:justify-start items-center gap-3">
          <i class="fas fa-phone"></i>
          <a href="tel:+62351323456" class="hover:text-white text-sm">0800-1234-5678</a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Garis Pembatas -->
  <div class="border-t border-blue-500 mt-8"></div>

  <!-- Copyright -->
  <div class="text-center py-4 text-sm text-gray-200">
    &copy; 2025 UPT Komputer UNIPMA — <span class="font-semibold">Tugas Kelompok RPL</span>. All rights reserved.
  </div>
</footer>


    <!-- Script Toggle Hamburger -->
    <script>
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const hamburgerIcon = document.getElementById('hamburgerIcon');
        const closeIcon = document.getElementById('closeIcon');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            hamburgerIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                mobileMenu.classList.add('hidden');
                hamburgerIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        });
    </script>
</body>

</html>