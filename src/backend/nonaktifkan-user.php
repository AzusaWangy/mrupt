<?php
include "koneksi.php";
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['Level'] !== 'admin') {
    header("Location: ../formlogin.php");
    exit;
}

if (isset($_GET['id'])) {
    $userID = $_GET['id'];

    $sql = "UPDATE login SET status = 'tidak aktif' WHERE UserID = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);
}

header("Location: ../kelola-user.php");
exit;
