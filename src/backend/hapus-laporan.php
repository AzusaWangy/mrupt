<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['Level'] != 'admin') {
  header("Location: ../formlogin.php");
  exit;
}

if (!isset($_GET['id'])) {
  header("Location: ../manajemen-laporan.php");
  exit;
}

$id_mr = $_GET['id'];

// Mulai transaction
$koneksi->begin_transaction();

try {
  // 1. Hapus data perangkat yang terkait dengan laporan MR
  $delete_perangkat = $koneksi->prepare("DELETE FROM perangkat WHERE id_mr = ?");
  $delete_perangkat->bind_param("i", $id_mr);
  $delete_perangkat->execute();
  $delete_perangkat->close();
  
  // 2. Hapus data terkait lainnya
  $delete_keluhan = $koneksi->prepare("DELETE FROM keluhan WHERE id_mr = ?");
  $delete_keluhan->bind_param("i", $id_mr);
  $delete_keluhan->execute();
  $delete_keluhan->close();
  
  $delete_perbaikan = $koneksi->prepare("DELETE FROM perbaikan WHERE id_mr = ?");
  $delete_perbaikan->bind_param("i", $id_mr);
  $delete_perbaikan->execute();
  $delete_perbaikan->close();
  
  $delete_lampiran = $koneksi->prepare("DELETE FROM lampiran WHERE id_mr = ?");
  $delete_lampiran->bind_param("i", $id_mr);
  $delete_lampiran->execute();
  $delete_lampiran->close();
  
  // 3. Hapus laporan utama
  $delete_laporan = $koneksi->prepare("DELETE FROM laporan_mr WHERE id_mr = ?");
  $delete_laporan->bind_param("i", $id_mr);
  $delete_laporan->execute();
  $delete_laporan->close();
  
  // Commit transaction
  $koneksi->commit();
  
  header("Location: ../manajemen-laporan.php?success=Laporan dan perangkat terkait berhasil dihapus");
  exit;
  
} catch (Exception $e) {
  // Rollback transaction jika ada error
  $koneksi->rollback();
  header("Location: ../manajemen-laporan.php?error=Gagal menghapus laporan: " . urlencode($e->getMessage()));
  exit;
}
?>