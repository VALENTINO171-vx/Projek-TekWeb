<?php
include 'connection.php';
include 'header.php';

$id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT b.*, f.code AS flight_code, f.origin, f.destination, f.departure
    FROM booking b
    LEFT JOIN flight f ON b.flight_id = f.id
    WHERE b.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo "<h3>Booking tidak ditemukan</h3>";
    include 'footer.php';
    exit;
}
?>

<div class="container p-4">
    <h3>Detail Booking #<?= $data['id'] ?></h3>

    <table class="table table-bordered">
        <tr><th>Nama</th><td><?= htmlspecialchars($data['passenger_name']) ?></td></tr>
        <tr><th>No Telp</th><td><?= htmlspecialchars($data['passenger_phone']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($data['passenger_email']) ?></td></tr>
        <tr><th>Flight</th><td><?= $data['flight_code'] ?> (<?= $data['origin'] ?> → <?= $data['destination'] ?>)</td></tr>
        <tr><th>Kursi</th><td><?= $data['seat_no'] ?></td></tr>
        <tr><th>Waktu Booking</th><td><?= $data['created_at'] ?></td></tr>
    </table>

    <a href="select_seat.php?flight_id=<?= $data['flight_id'] ?>" class="btn btn-secondary">Booking Kursi Lain</a>
</div>

<?php include 'footer.php'; ?>
