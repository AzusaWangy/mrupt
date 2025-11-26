<?php
include "./koneksi.php";
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['Level'] !== 'admin') {
    header("Location: formlogin.php");
    exit;
}

if (isset($_POST['user_id'])) {
    $userID = $_POST['user_id'];

    $sql = "UPDATE login SET status = 'aktif' WHERE UserID = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userID);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "User berhasil diaktifkan!";
    } else {
        $_SESSION['message'] = "Gagal mengaktifkan user!";
    }

    mysqli_stmt_close($stmt);
}

header("Location: admin_users.php");
exit;
