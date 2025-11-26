<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['Level'] != 'admin') {
  header("Location: ../formlogin.php");
  exit;
}

if (isset($_GET['id'])) {
    $id_petugas = $_GET['id'];

    // Query hapus
    $query = "DELETE FROM petugas WHERE id_petugas = '$id_petugas'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../petugas.php?status=deleted");
        exit;
    } else {
        echo "Gagal menghapus data: " . mysqli_error($koneksi);
    }
} else {
    echo "ID tidak ditemukan.";
}
?>
