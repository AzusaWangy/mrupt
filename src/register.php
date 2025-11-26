<?php
include 'backend/koneksi.php';
session_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar | Sistem MR UPT Komputer</title>
  <link rel="icon" type="image/png" href="assets/1.png" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 via-sky-700 to-blue-600 p-6 font-sans">

  <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-md transition-all">
    <!-- Judul -->
    <h2 class="text-3xl font-bold text-center text-blue-700 mb-2">Daftar Akun</h2>
    <p class="text-center text-gray-500 mb-8 text-sm">Buat akun baru untuk mengakses Sistem MR UPT Komputer</p>

    <!-- Form -->
    <form action="backend/proses-regist.php" method="POST" class="space-y-5">
      <!-- Username -->
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="Username" id="username"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
          placeholder="Masukkan username" required>
      </div>

      <!-- Nama Lengkap -->
      <div>
        <label for="nama_user" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
        <input type="text" name="nama_user" id="nama_user"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
          placeholder="Masukkan nama lengkap" required>
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="Password" id="password"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
          placeholder="Masukkan password" required>
      </div>

      <!-- Unit -->
      <div>
        <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Unit/Biro/Lembaga/Fakultas/Prodi</label>
        <select id="unit" name="id_unit"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
          required>
          <option value="" disabled selected>Pilih unit</option>
          <?php
          $unit = mysqli_query($koneksi, "SELECT * FROM unit");
          while ($b = mysqli_fetch_array($unit)) {
            echo "<option value='{$b['id_unit']}'>{$b['nama_unit']}</option>";
          }
          ?>
          <option value="99">Lainnya</option>
        </select>
        <p class="text-xs text-gray-500 mt-1">*Bila unit tidak ada, pilih "Lainnya"</p>
      </div>

      <!-- Unit Lain -->
      <div id="unitLainContainer" class="hidden">
        <label for="unitLain" class="block text-sm font-medium text-gray-700 mb-1">Unit Lainnya</label>
        <input type="text" id="unitLain" name="unitLain"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
          placeholder="Isi jika unit tidak ada di daftar">
      </div>

      <!-- Pesan Error -->
      <?php if (!empty($_SESSION['register_errors'])): ?>
        <div class="p-3 bg-red-100 border border-red-300 rounded-lg text-red-700 text-sm mt-4">
          <div class="flex items-start">
            <i class="fa-solid fa-circle-exclamation mt-0.5 mr-2"></i>
            <div>
              <?php foreach ($_SESSION['register_errors'] as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php unset($_SESSION['register_errors']); ?>
      <?php endif; ?>

      <!-- Tombol -->
      <button type="submit"
        class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition duration-200">
        Daftar Sekarang
      </button>
    </form>

    <div class="text-center text-sm text-gray-600 mt-6 flex justify-center items-center gap-1 flex-wrap">
      <p>Sudah punya akun?</p>
      <a href="./formlogin.php" class="text-blue-600 font-medium hover:underline">Login</a>
    </div>
  </div>

  <!-- Script toggle input unit lainnya -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const unitSelect = document.getElementById('unit');
      const unitLainContainer = document.getElementById('unitLainContainer');
      const unitLainInput = document.getElementById('unitLain');

      function toggleUnitLain() {
        if (unitSelect.value === '99') {
          unitLainContainer.classList.remove('hidden');
          unitLainInput.required = true;
        } else {
          unitLainContainer.classList.add('hidden');
          unitLainInput.required = false;
        }
      }

      unitSelect.addEventListener('change', toggleUnitLain);
      toggleUnitLain();
    });
  </script>

</body>
</html>
