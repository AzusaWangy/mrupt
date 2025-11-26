<?php
include "./koneksi.php";
session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Gunakan prepared statement untuk mencegah SQL injection
$sql = "SELECT * FROM login WHERE Username = ? LIMIT 1";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {

    // ✅ CEK STATUS DULU
    if ($row['status'] !== 'aktif') {
        $_SESSION['login_error'] = "Akun belum aktif! Silakan tunggu persetujuan admin.";
        header("Location: ../formlogin.php");
        exit;
    }

    // ✅ Verifikasi password
    if (password_verify($password, $row['Password'])) {
        $_SESSION['user'] = [
            'UserID'    => $row['UserID'],
            'Username'  => $row['Username'],
            'Level'     => $row['Level'],   // admin/user/pelapor
            'nama_user' => $row['nama_user']
        ];
        $_SESSION['login_success'] = true;
        header("Location: ../dashboard.php");
        exit;
    } else {
        $_SESSION['login_error'] = "Username atau password salah!";
        header("Location: ../formlogin.php");
        exit;
    }
} else {
    $_SESSION['login_error'] = "Username tidak ditemukan!";
    header("Location: ../formlogin.php");
    exit;
}

mysqli_stmt_close($stmt);
?>
