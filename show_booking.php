<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("ID booking tidak ditemukan.");
}

$booking_id = $_GET['id'];

// Ambil data booking + penerbangan
$query = "
SELECT b.*, p.kode_penerbangan, p.asal, p.tujuan, p.tanggal_berangkat, p.jam_berangkat, p.harga
FROM booking b
JOIN penerbangan p ON b.flight_id = p.id
WHERE b.id = $booking_id
";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data booking tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Booking</title>
</head>
<body>

<h2>Detail Booking Berhasil</h2>

<p><b>Nama:</b> <?= $data['name'] ?></p>
<p><b>Email:</b> <?= $data['email'] ?></p>
<p><b>Nomor HP:</b> <?= $data['phone'] ?></p>
<p><b>Kursi:</b> <?= $data['seat_number'] ?></p>

<hr>

<h3>Detail Penerbangan</h3>

<p><b>Kode:</b> <?= $data['kode_penerbangan'] ?></p>
<p><b>Asal:</b> <?= $data['asal'] ?></p>
<p><b>Tujuan:</b> <?= $data['tujuan'] ?></p>
<p><b>Tanggal:</b> <?= $data['tanggal_berangkat'] ?></p>
<p><b>Jam Berangkat:</b> <?= $data['jam_berangkat'] ?></p>
<p><b>Harga:</b> Rp<?= number_format($data['harga'], 0, ',', '.') ?></p>

</body>
</html>
