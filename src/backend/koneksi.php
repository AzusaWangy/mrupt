<?php
$hostname = 'localhost';
$username = 'root';      
$password = '';    
$database = 'mr_upt';

$koneksi = mysqli_connect($hostname, $username, $password, $database);

if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

$koneksi->set_charset("utf8");
?>