<?php
include 'connection.php';
include 'header.php';

// validate id
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo '<div class="container p-4"><div class="alert alert-danger">Invalid booking id.</div></div>';
    include 'footer.php';
    exit;
}

// require login
if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to view bookings.';
    header('Location: login.php');
    exit;
}
$uid = intval($_SESSION['user_id']);

// fetch booking joined to penerbangan and seat
$sql = "
    SELECT b.*, p.kode_penerbangan, p.asal, p.tujuan, p.tanggal_berangkat, p.jam_berangkat, p.jam_tiba,
           s.seat_no
    FROM booking b
    LEFT JOIN penerbangan p ON b.flight_id = p.id
    LEFT JOIN seat s ON b.seat_id = s.id
    WHERE b.id = ?
    LIMIT 1
";

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
    echo '<div class="container p-4"><div class="alert alert-danger">You are not authorized to view this booking.</div></div>';
    include 'footer.php';
    exit;
}
?>

<div class="container p-4">
    <h3>Detail Booking #<?= (int)$data['id'] ?></h3>

    <table class="table table-bordered">
        <tr><th>Nama</th><td><?= htmlspecialchars($data['passenger_name'] ?? '') ?></td></tr>
        <tr><th>No Telp</th><td><?= htmlspecialchars($data['passenger_phone'] ?? '') ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($data['passenger_email'] ?? '') ?></td></tr>
        <tr><th>Flight</th><td><?= htmlspecialchars(($data['kode_penerbangan'] ?? '') . ' — ' . ($data['asal'] ?? '') . ' → ' . ($data['tujuan'] ?? '')) ?></td></tr>
        <tr><th>Tanggal</th><td><?= htmlspecialchars($data['tanggal_berangkat'] ?? '') ?></td></tr>
        <tr><th>Jam Keberangkatan</th><td><?= htmlspecialchars($data['jam_berangkat'] ?? '') ?></td></tr>
        <tr><th>Kursi</th><td><?= htmlspecialchars($data['seat_no'] ?? 'N/A') ?></td></tr>
        <tr><th>Waktu Booking</th><td><?= htmlspecialchars($data['created_at'] ?? '') ?></td></tr>
    </table>

    <a href="booking.php?flight_id=<?= (int)($data['flight_id'] ?? 0) ?>" class="btn btn-secondary">Booking Kursi Lain</a>
</div>

<?php include 'footer.php'; ?>
```// filepath: d:\laragon\www\Projek-TekWeb\show_booking.php
<?php
include 'connection.php';
include 'header.php';
session_start();

// validate id
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo '<div class="container p-4"><div class="alert alert-danger">Invalid booking id.</div></div>';
    include 'footer.php';
    exit;
}

// require login
if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to view bookings.';
    header('Location: login.php');
    exit;
}
$uid = intval($_SESSION['user_id']);

// fetch booking joined to penerbangan and seat
$sql = "
    SELECT b.*, p.kode_penerbangan, p.asal, p.tujuan, p.tanggal_berangkat, p.jam_berangkat, p.jam_tiba,
           s.seat_no
    FROM booking b
    LEFT JOIN penerbangan p ON b.flight_id = p.id
    LEFT JOIN seat s ON b.seat_id = s.id
    WHERE b.id = ?
    LIMIT 1
";

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
    echo '<div class="container p-4"><div class="alert alert-danger">You are not authorized to view this booking.</div></div>';
    include 'footer.php';
    exit;
}
?>

<div class="container p-4">
    <h3>Detail Booking #<?= (int)$data['id'] ?></h3>

    <table class="table table-bordered">
        <tr><th>Nama</th><td><?= htmlspecialchars($data['passenger_name'] ?? '') ?></td></tr>
        <tr><th>No Telp</th><td><?= htmlspecialchars($data['passenger_phone'] ?? '') ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($data['passenger_email'] ?? '') ?></td></tr>
        <tr><th>Flight</th><td><?= htmlspecialchars(($data['kode_penerbangan'] ?? '') . ' — ' . ($data['asal'] ?? '') . ' → ' . ($data['tujuan'] ?? '')) ?></td></tr>
        <tr><th>Tanggal</th><td><?= htmlspecialchars($data['tanggal_berangkat'] ?? '') ?></td></tr>
        <tr><th>Jam Keberangkatan</th><td><?= htmlspecialchars($data['jam_berangkat'] ?? '') ?></td></tr>
        <tr><th>Kursi</th><td><?= htmlspecialchars($data['seat_no'] ?? 'N/A') ?></td></tr>
        <tr><th>Waktu Booking</th><td><?= htmlspecialchars($data['created_at'] ?? '') ?></td></tr>
    </table>

    <a href="booking.php?flight_id=<?= (int)($data['flight_id'] ?? 0) ?>" class="btn btn-secondary">Booking Kursi Lain</a>
</div>

<?php include 'footer.php'; ?>