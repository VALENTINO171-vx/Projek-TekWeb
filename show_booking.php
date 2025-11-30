<?php
include 'connection.php';
include 'header.php';

// validate id param
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo '<div class="container p-4"><div class="alert alert-danger">ID booking tidak ditemukan.</div></div>';
    include 'footer.php';
    exit;
}

// require login
if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Silakan login untuk melihat booking Anda.';
    header('Location: login.php');
    exit;
}
$uid = intval($_SESSION['user_id']);

// fetch booking joined to penerbangan and seat
$sql = "
SELECT b.*, p.kode_penerbangan, p.asal, p.tujuan, p.tanggal_berangkat, p.jam_berangkat, p.harga,
       s.seat_no
FROM booking b
LEFT JOIN penerbangan p ON b.flight_id = p.id
LEFT JOIN seat s ON b.seat_id = s.id
WHERE b.id = ?
LIMIT 1
";

$data = null;
if ($conn instanceof PDO) {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res ? $res->fetch_assoc() : null;
    $stmt->close();
}

if (!$data) {
    echo '<div class="container p-4"><div class="alert alert-danger">Booking tidak ditemukan.</div></div>';
    include 'footer.php';
    exit;
}

// authorize: only owner can view
if (intval($data['passenger_id'] ?? 0) !== $uid) {
    echo '<div class="container p-4"><div class="alert alert-danger">Anda tidak berhak melihat booking ini.</div></div>';
    include 'footer.php';
    exit;
}

// render booking details (use actual column names)
?>
<div class="container p-4">
  <h2>Detail Booking #<?= (int)$data['id'] ?></h2>

  <p><b>Nama:</b> <?= htmlspecialchars($data['passenger_name'] ?? '') ?></p>
  <p><b>Email:</b> <?= htmlspecialchars($data['passenger_email'] ?? '') ?></p>
  <p><b>Nomor HP:</b> <?= htmlspecialchars($data['passenger_phone'] ?? '') ?></p>
  <p><b>Kursi:</b> <?= htmlspecialchars($data['seat_no'] ?? 'N/A') ?></p>

  <hr>

  <h3>Detail Penerbangan</h3>
  <p><b>Kode:</b> <?= htmlspecialchars($data['kode_penerbangan'] ?? '') ?></p>
  <p><b>Asal:</b> <?= htmlspecialchars($data['asal'] ?? '') ?></p>
  <p><b>Tujuan:</b> <?= htmlspecialchars($data['tujuan'] ?? '') ?></p>
  <p><b>Tanggal:</b> <?= htmlspecialchars($data['tanggal_berangkat'] ?? '') ?></p>
  <p><b>Jam Keberangkatan:</b> <?= htmlspecialchars($data['jam_berangkat'] ?? '') ?></p>
  <p><b>Harga:</b> Rp<?= number_format($data['harga'] ?? 0, 0, ',', '.') ?></p>

  <a class="btn btn-secondary" href="booking.php?flight_id=<?= (int)($data['flight_id'] ?? 0) ?>">Booking Kursi Lain</a>
</div>

<?php include 'footer.php'; ?>