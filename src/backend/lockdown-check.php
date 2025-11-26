<?php
// lockdown-check.php
session_start();

// Check jika user mencoba akses langsung tanpa lockdown
if (!isset($_SESSION['user']) && basename($_SERVER['PHP_SELF']) !== 'index.php') {
    header("Location: index.php?pesan=lockdown_akses_ditolak");
    exit;
}

// Set lockdown flag di session
$_SESSION['lockdown_active'] = true;
?>