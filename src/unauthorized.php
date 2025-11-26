<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - UPT Komputer UNIPMA</title>
   <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/1.png" />
</head>

<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex items-center justify-center font-sans">

    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full mx-4 text-center">
        <!-- Icon -->
        <div class="mb-6">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                <i class="fas fa-ban text-red-500 text-3xl"></i>
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-2xl font-bold text-gray-800 mb-3">Akses Ditolak</h1>

        <!-- Message -->
        <p class="text-gray-600 mb-2">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <p class="text-sm text-gray-500 mb-6">Halaman ini hanya dapat diakses oleh Administrator.</p>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <a href="dashboard.php" 
               class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                <i class="fas fa-home mr-2"></i>Kembali ke Beranda
            </a>

            <?php if (isset($_SESSION['user'])): ?>
                <a href="logout.php" 
                   class="w-full bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-sign-out-alt mr-2"></i>Login sebagai User Lain
                </a>
            <?php else: ?>
                <a href="formlogin.php" 
                   class="w-full bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login Kembali
                </a>
            <?php endif; ?>
        </div>

        <!-- Contact Info -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500 mb-2">Butuh bantuan?</p>
            <div class="flex justify-center space-x-4 text-xs text-gray-500">
                <div class="flex items-center">
                    <i class="fas fa-envelope mr-1"></i>
                    <span>uptkomputer@unipma.ac.id</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-phone mr-1"></i>
                    <span>(0351) 123456</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional: Floating background elements -->
    <div class="fixed -z-10 top-10 left-10 w-32 h-32 bg-blue-200 rounded-full opacity-20 blur-xl"></div>
    <div class="fixed -z-10 bottom-10 right-10 w-40 h-40 bg-red-200 rounded-full opacity-20 blur-xl"></div>

</body>

</html>