<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("ID penerbangan tidak ditemukan.");
}

$flight_id = $_GET['id'];
$query = "SELECT * FROM penerbangan WHERE id = $flight_id";
$result = mysqli_query($conn, $query);
$flight = mysqli_fetch_assoc($result);

if (!$flight) {
    die("Penerbangan tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking</title>
</head>
<body>

<h2>Booking Penerbangan</h2>

<p><b>Kode:</b> <?= $flight['kode_penerbangan'] ?></p>
<p><b>Asal:</b> <?= $flight['asal'] ?></p>
<p><b>Tujuan:</b> <?= $flight['tujuan'] ?></p>
<p><b>Tanggal:</b> <?= $flight['tanggal_berangkat'] ?></p>
<p><b>Jam Berangkat:</b> <?= $flight['jam_berangkat'] ?></p>
<p><b>Harga:</b> Rp<?= number_format($flight['harga'], 0, ',', '.') ?></p>

<hr>

<form action="booking_action.php" method="POST">
    <input type="hidden" name="flight_id" value="<?= $flight['id'] ?>">

    Nama: <br>
    <input type="text" name="name" required><br><br>

    Email: <br>
    <input type="email" name="email" required><br><br>

    Nomor HP: <br>
    <input type="text" name="phone" required><br><br>

    Pilih Kursi (ex: A1, B2):<br>
    <input type="text" name="seat_number" required><br><br>

    <button type="submit">Submit Booking</button>
</form>

</body>
</html>
