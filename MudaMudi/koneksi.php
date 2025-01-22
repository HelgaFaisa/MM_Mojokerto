<?php
$host = 'localhost'; // Nama host (localhost jika di server lokal)
$user = 'root'; // Username database MySQL Anda
$pass = ''; // Password database MySQL Anda
$dbname = 'mudamudi'; // Nama database Anda

// Membuat koneksi ke database
$config = mysqli_connect($host, $user, $pass, $dbname);

// Cek koneksi
if (!$config) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
