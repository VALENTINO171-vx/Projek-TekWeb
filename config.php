<?php
// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$password = ''; // Kosongkan untuk Laragon default
$database = 'petra_airlines';

// Koneksi ke Database
$conn = mysqli_connect($host, $user, $password, $database);

// Cek Koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($conn, "utf8");
?>