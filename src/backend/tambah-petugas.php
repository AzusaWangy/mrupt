<?php
include "koneksi.php";
session_start();

// Hanya admin yang boleh tambah petugas
if (!isset($_SESSION['user']) || $_SESSION['user']['Level'] !== 'admin') {
    header("Location: ../formlogin.php");
    exit;
}

// Proses jika form dikirim
if (isset($_POST['nama_petugas'])) {
    $nama = trim($_POST['nama_petugas']);

    // Validasi input tidak boleh kosong
    if ($nama === '') {
        $_SESSION['message'] = "Nama petugas tidak boleh kosong.";
        header("Location: ../petugas.php");
        exit;
    }

    // Cek apakah nama sudah ada di tabel petugas
    $cek = mysqli_prepare($koneksi, "SELECT id_petugas FROM petugas WHERE nama_petugas = ?");
    mysqli_stmt_bind_param($cek, "s", $nama);
    mysqli_stmt_execute($cek);
    $result = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "Nama petugas sudah terdaftar.";
        header("Location: ../petugas.php");
        exit;
    }

    mysqli_stmt_close($cek);

    // Insert ke tabel petugas
    $stmt = mysqli_prepare($koneksi, "INSERT INTO petugas (nama_petugas) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $nama);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Petugas berhasil ditambahkan.";
    } else {
        $_SESSION['message'] = "Gagal menambahkan petugas.";
    }

    mysqli_stmt_close($stmt);
}

// Redirect kembali ke halaman petugas
header("Location: ../petugas.php");
exit;
