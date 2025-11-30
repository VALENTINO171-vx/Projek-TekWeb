<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connection.php';
include 'header.php';

// Validate ID
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "<div class='container p-4 alert alert-danger'>ID booking tidak ditemukan.</div>";
    include 'footer.php';
    exit;
}

// Fetch booking + flight + seat info
$sql = "
SELECT 
    b.id,
    b.name,
    b.email,
    b.phone,
    b.seat_number,
    b.flight_id,
    
    p.kode_penerbangan,
    p.asal,
    p.tujuan,
    p.tanggal_berangkat,
    p.jam_berangkat,
    p.jam_tiba,
    p.harga,
    
    s.class AS seat_class

FROM booking b
LEFT JOIN penerbangan p ON p.id = b.flight_id
LEFT JOIN seat s 
       ON s.flight_id = b.flight_id
      AND s.seat_no = b.seat_number

WHERE b.id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$stmt->close();

// Not found
if (!$data) {
    echo "<div class='container p-4 alert alert-danger'>Booking tidak ditemukan.</div>";
    include 'footer.php';
    exit;
}
?>

<div class="container p-4">
    <h2>Detail Booking #<?= htmlspecialchars($data['id']) ?></h2>

    <p><b>Nama:</b> <?= htmlspecialchars($data['name']) ?></p>
    <p><b>Email:</b> <?= htmlspecialchars($data['email']) ?></p>
    <p><b>Nomor HP:</b> <?= htmlspecialchars($data['phone']) ?></p>
    <p><b>Kursi:</b> <?= htmlspecialchars($data['seat_number']) ?></p>
    <p><b>Kelas Kursi:</b> <?= htmlspecialchars($data['seat_class'] ?? 'Economy') ?></p>

    <hr>

    <h3>Detail Penerbangan</h3>
    <p><b>Kode Penerbangan:</b> <?= htmlspecialchars($data['kode_penerbangan']) ?></p>
    <p><b>Asal:</b> <?= htmlspecialchars($data['asal']) ?></p>
    <p><b>Tujuan:</b> <?= htmlspecialchars($data['tujuan']) ?></p>
    <p><b>Tanggal:</b> <?= htmlspecialchars($data['tanggal_berangkat']) ?></p>
    <p><b>Jam Berangkat:</b> <?= htmlspecialchars($data['jam_berangkat']) ?></p>
    <p><b>Jam Tiba:</b> <?= htmlspecialchars($data['jam_tiba']) ?></p>
    <p><b>Harga:</b> Rp<?= number_format($data['harga'], 0, ',', '.') ?></p>

    <a class="btn btn-secondary mt-3" href="booking.php?flight_id=<?= (int)$data['flight_id'] ?>">
        Booking Kursi Lain
    </a>
</div>

<?php include 'footer.php'; ?>
