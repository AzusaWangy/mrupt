<?php
include '../backend/koneksi.php';
session_start();

// Generate nomor MR di backend
function generateNoMR($koneksi) {
    $year = date('Y');
    $query = "SELECT MAX(CAST(SUBSTRING(nomor_mr, 9) AS UNSIGNED)) as lastNumber 
              FROM laporan_mr 
              WHERE YEAR(tanggal_laporan) = $year 
              AND nomor_mr LIKE 'MR-$year-%'";
    
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    $lastNumber = $data['lastNumber'] ? (int)$data['lastNumber'] : 0;
    
    return "MR-$year-" . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data dari form
    $noMR = generateNoMR($koneksi);
    // $noMR       = $_POST['noMR'];
    $nomor      = $_POST['nomorSurat'];
    $keperluan  = $_POST['keperluanSurat'];
    $tgl_lap    = $_POST['tanggalLaporan'];
    $tgl_cek    = $_POST['tanggalPengecekan'];
    $status     = $_POST['status'];
    $id_unit    = $_POST['id_unit'];
    $unitLain   = trim($_POST['unitLain']);
    $id_petugas = $_POST['id_petugas']; // Sekarang ini adalah ID numerik
    $id_user    = $_SESSION['user']['UserID'];

    // Ambil array multi input
    $perangkat  = $_POST['perangkat'] ?? [];
    $keluhan    = $_POST['keluhan'] ?? [];
    $hasilPengecekan = $_POST['hasilPengecekan'] ?? [];
    $perbaikan  = $_POST['perbaikan'] ?? [];

    // Handle upload file lampiran (jika ada)
$lampiranPath = null;
if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
    $lampiran = $_FILES['lampiran'];
    
    // Validasi ukuran file (max 10MB)
    $maxSize = 10 * 1024 * 1024;
    if ($lampiran['size'] > $maxSize) {
         echo "<script>alert('Ukuran file maksimal 10MB!'); window.history.back();</script>";
    exit;

    }
    
    // Validasi ekstensi & tipe MIME
    $ext = strtolower(pathinfo($lampiran['name'], PATHINFO_EXTENSION));
    $allowedExt = ['pdf','jpg','jpeg','png','doc','docx'];
    $allowedMime = [
        'application/pdf',
        'image/jpeg', 
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    // Cek kedua-duanya untuk keamanan lebih
    if (in_array($ext, $allowedExt) && in_array($lampiran['type'], $allowedMime)) {
        $newName = uniqid('lampiran_') . '.' . $ext;
        $uploadDir = '../uploads/lampiran/';
        
        // Buat directory dengan error handling
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new Exception("Gagal membuat direktori upload");
        }
        
        $uploadPath = $uploadDir . $newName;
        
        if (move_uploaded_file($lampiran['tmp_name'], $uploadPath)) {
            $lampiranPath = 'uploads/lampiran/' . $newName;
        } else {
            throw new Exception("Gagal mengupload file");
        }
    } else {
    echo "<script>alert('Tipe file tidak diizinkan!'); window.history.back();</script>";
    exit;
}
}

    // Jika unitLain diisi, simpan unit baru dan gunakan id baru
  // VALIDASI ID_UNIT - Pastikan id_unit valid
  if (!empty($unitLain)) {
    // Jika unitLain diisi, buat unit baru
    $stmtUnit = $koneksi->prepare("INSERT INTO unit (nama_unit) VALUES (?)");
    $stmtUnit->bind_param("s", $unitLain);
    if ($stmtUnit->execute()) {
        $id_unit = $stmtUnit->insert_id;
    } else {
        echo "<script>alert('Gagal membuat unit baru: " . addslashes($stmtUnit->error) . "'); window.history.back();</script>";
        exit;
    }
    $stmtUnit->close();
} else {
    // Validasi bahwa id_unit yang dipilih benar-benar ada di database
    $checkUnit = $koneksi->prepare("SELECT id_unit FROM unit WHERE id_unit = ?");
    $checkUnit->bind_param("i", $id_unit);
    $checkUnit->execute();
    $checkUnit->store_result();
    
    if ($checkUnit->num_rows == 0) {
        echo "<script>alert('Unit yang dipilih tidak valid!'); window.history.back();</script>";
        exit;
    }
    $checkUnit->close();
}

    // Gabungkan hasil pengecekan jadi string
    $hasil = implode(", ", $hasilPengecekan);

    // Mulai transaction
    $koneksi->begin_transaction();

    try {
        // Insert ke tabel laporan_mr
        $insert_stmt = $koneksi->prepare("INSERT INTO laporan_mr 
            (nomor_mr, nomor_surat, keperluan_surat, tanggal_laporan, tanggal_pengecekan, hasil_pengecekan, status, id_unit, id_user) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $insert_stmt->bind_param(
            "sssssssii",
            $noMR,
            $nomor,
            $keperluan,  
            $tgl_lap,
            $tgl_cek,
            $hasil,
            $status,
            $id_unit,
            $id_user
        );

        if (!$insert_stmt->execute()) {
            throw new Exception("Gagal menyimpan laporan: " . $insert_stmt->error);
        }
        
        $id_mr = $insert_stmt->insert_id;
        $insert_stmt->close();

        // Insert lampiran jika ada
        if ($lampiranPath) {
            $insert_lampiran = $koneksi->prepare("INSERT INTO lampiran (id_mr, nama_file) VALUES (?, ?)");
            $insert_lampiran->bind_param("is", $id_mr, $lampiranPath);
            if (!$insert_lampiran->execute()) {
                throw new Exception("Gagal menyimpan lampiran: " . $insert_lampiran->error);
            }
            $insert_lampiran->close();
        }

        // Insert keluhan
        foreach ($keluhan as $k) {
            if (!empty(trim($k))) {
                $insert_keluhan = $koneksi->prepare("INSERT INTO keluhan (id_mr, deskripsi_keluhan) VALUES (?, ?)");
                $insert_keluhan->bind_param("is", $id_mr, $k);
                if (!$insert_keluhan->execute()) {
                    throw new Exception("Gagal menyimpan keluhan: " . $insert_keluhan->error);
                }
                $insert_keluhan->close();
            }
        }

        // Insert perbaikan
        foreach ($perbaikan as $p) {
            if (!empty(trim($p))) {
                $insert_perbaikan = $koneksi->prepare("INSERT INTO perbaikan (id_mr, id_petugas, deskripsi_perbaikan) VALUES (?, ?, ?)");
                $insert_perbaikan->bind_param("iis", $id_mr, $id_petugas, $p);
                if (!$insert_perbaikan->execute()) {
                    throw new Exception("Gagal menyimpan perbaikan: " . $insert_perbaikan->error);
                }
                $insert_perbaikan->close();
            }
        }

       // Insert perangkat
foreach ($perangkat as $p) {
    if (!empty(trim($p))) {
        $insert_perangkat = $koneksi->prepare("INSERT INTO perangkat (id_unit, nama_perangkat, id_mr) VALUES (?, ?, ?)");
        $insert_perangkat->bind_param("isi", $id_unit, $p, $id_mr);
        if (!$insert_perangkat->execute()) {
            throw new Exception("Gagal menyimpan perangkat: " . $insert_perangkat->error);
        }
        $insert_perangkat->close();
    }
}

        // Commit transaction
        $koneksi->commit();
        
        echo "<script>alert('Berhasil Input! Nomor MR: $noMR'); window.location.href = '../dashboard.php';</script>";
        
    } catch (Exception $e) {
        // Rollback transaction jika ada error
        $koneksi->rollback();
        
        // Hapus file yang sudah diupload jika transaction gagal
        if ($lampiranPath && file_exists('../' . $lampiranPath)) {
            unlink('../' . $lampiranPath);
        }
        
        echo "<script>alert('Input gagal: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
}
?>