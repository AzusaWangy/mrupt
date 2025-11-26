<?php
session_start();
if (isset($_GET['pesan']) && $_GET['pesan'] == "belum_login") {
  echo '
    <div class="fixed top-5 left-1/2 -translate-x-1/2 z-50">
      <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-6 py-4 rounded-lg shadow-lg flex items-center gap-2">
        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
        </svg>
        <span class="font-semibold">Anda harus login dulu!</span>
      </div>
    </div>

    <script>
      setTimeout(() => {
        const alertBox = document.querySelector(".fixed");
        if (alertBox) {
          alertBox.style.transition = "opacity 0.6s";
          alertBox.style.opacity = "0";
          setTimeout(() => alertBox.remove(), 600);
        }
      }, 3000);
    </script>
  ';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Sistem MR UPT Komputer</title>
  <link rel="icon" type="image/png" href="assets/1.png" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-800 via-blue-700 to-sky-600 font-sans px-4 sm:px-0">

  <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm sm:max-w-md p-8 sm:p-10">
    
    <!-- Logo -->
    <div class="flex justify-center mb-4">
      <img src="assets/logo.png" alt="Logo UNIPMA" class="w-14 h-14 object-contain">
    </div>

    <!-- Judul -->
    <h2 class="text-2xl sm:text-3xl font-bold text-center text-blue-800 mb-2">
      Selamat Datang di <br> UPT Komputer
    </h2>
    <p class="text-center text-gray-600 font-medium mb-8 text-sm sm:text-base">
      Silakan login terlebih dahulu
    </p>

    <!-- Form Login -->
    <form action="./backend/login.php" method="POST" class="space-y-5">
      <!-- Username -->
      <div>
        <label for="username" class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
        <input type="text" name="username" id="username"
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Masukkan Username" required>
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
        <input type="password" name="password" id="password"
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Masukkan Password" required>
      </div>

      <!-- Error Alert -->
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="flex items-start gap-2 text-red-600 bg-red-50 border border-red-300 px-3 py-2 rounded-lg text-sm">
          <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
          </svg>
          <p><?= htmlspecialchars($_SESSION['error']); ?></p>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="flex items-start gap-2 text-red-600 bg-red-50 border border-red-300 px-3 py-2 rounded-lg text-sm">
          <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
          </svg>
          <p><?= htmlspecialchars($_SESSION['login_error']); ?></p>
        </div>
        <?php unset($_SESSION['login_error']); ?>
      <?php endif; ?>

      <!-- Tombol Login -->
      <button type="submit"
        class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-all duration-300 text-sm sm:text-base">
        Login
      </button>
    </form>

    <!-- Link Daftar -->
    <div class="text-center text-sm text-gray-600 mt-6 flex justify-center items-center gap-1 flex-wrap">
      <p>Belum punya akun?</p>
      <a href="register.php" class="text-blue-600 font-semibold hover:underline">Daftar</a>
    </div>
  </div>

</body>

</html>
