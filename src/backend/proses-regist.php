<?php
session_start();
include './koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header('Location: ../register.php');
    exit();
}

$user = $_POST['Username'] ?? '';
$pass = $_POST['Password'] ?? '';
$nama = $_POST['nama_user'] ?? '';
$id_unit = $_POST['id_unit'] ?? '';
$unitLain = trim($_POST['unitLain'] ?? '');

$_SESSION['register_errors'] = [];

// Validasi dasar
if (empty($user)) {
    $_SESSION['register_errors'][] = "Username tidak boleh kosong!";
}
if (strlen($pass) < 8) {
    $_SESSION['register_errors'][] = "Password harus minimal 8 karakter!";
}
if (empty($nama)) {
    $_SESSION['register_errors'][] = "Nama tidak boleh kosong!";
}

// Validasi unit
if (empty($id_unit)) {
    $_SESSION['register_errors'][] = "Unit harus dipilih!";
} else if ($id_unit == 99 && empty($unitLain)) {
    $_SESSION['register_errors'][] = "Jika memilih 'Lainnya', harus mengisi nama unit!";
} else if ($id_unit != 99 && !empty($unitLain)) {
    $_SESSION['register_errors'][] = "Hanya pilih 'Lainnya' jika unit tidak ada di daftar!";
}

// Cek username duplikat
if (!empty($user)) {
    $stmt = $koneksi->prepare("SELECT Username FROM login WHERE Username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['register_errors'][] = "Username '" . htmlspecialchars($user) . "' sudah terdaftar!";
    }
    $stmt->close();
}

// Jika ada error, redirect kembali
if (!empty($_SESSION['register_errors'])) {
    header('Location: ../register.php');
    exit();
}

// PROSES REGISTRASI - DENGAN HANDLING UNIT
try {
    $koneksi->begin_transaction();

    // Handle unit baru jika dipilih "Lainnya"
    $final_id_unit = $id_unit;
    
    if ($id_unit == 99 && !empty($unitLain)) {
        // Insert unit baru
        $stmtUnit = $koneksi->prepare("INSERT INTO unit (nama_unit) VALUES (?)");
        $stmtUnit->bind_param("s", $unitLain);
        if (!$stmtUnit->execute()) {
            throw new Exception("Gagal membuat unit baru: " . $stmtUnit->error);
        }
        $final_id_unit = $stmtUnit->insert_id;
        $stmtUnit->close();
    } else {
        // Validasi unit yang dipilih benar-benar ada
        $checkUnit = $koneksi->prepare("SELECT id_unit FROM unit WHERE id_unit = ?");
        $checkUnit->bind_param("i", $id_unit);
        $checkUnit->execute();
        $checkUnit->store_result();
        
        if ($checkUnit->num_rows == 0) {
            throw new Exception("Unit yang dipilih tidak valid!");
        }
        $checkUnit->close();
    }

    // Hash password dan insert user
    $hash_pass = password_hash($pass, PASSWORD_DEFAULT);
    $insert_stmt = $koneksi->prepare("INSERT INTO login (Username, Password, nama_user, id_unit, status) VALUES (?, ?, ?, ?, 'nonaktif')");
    $insert_stmt->bind_param("sssi", $user, $hash_pass, $nama, $final_id_unit);
    
    if (!$insert_stmt->execute()) {
        throw new Exception("Gagal menyimpan user: " . $insert_stmt->error);
    }
    
    // Commit transaction
    $koneksi->commit();
    
    $_SESSION['register_success'] = "Registrasi berhasil! Silakan login.";
    header('Location: ../formlogin.php');
    exit();
    
} catch (Exception $e) {
    // Rollback jika ada error
    $koneksi->rollback();
    $_SESSION['register_errors'][] = "Terjadi kesalahan: " . $e->getMessage();
    header('Location: ../register.php');
    exit();
}

$koneksi->close();
?>