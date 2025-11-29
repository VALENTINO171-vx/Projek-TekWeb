<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit;
}

$flight_id = intval($_POST['flight_id']);
$seat_no = $_POST['seat_no'];
$name = $_POST['passenger_name'];
$phone = $_POST['passenger_phone'];
$email = $_POST['passenger_email'];

/* Mulai transaksi */
$conn->beginTransaction();

try {
    // cek seat available (lock row)
    $stmt = $conn->prepare("SELECT status FROM seat WHERE flight_id = ? AND seat_no = ? FOR UPDATE");
    $stmt->execute([$flight_id, $seat_no]);
    $seatData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seatData) {
        throw new Exception("Seat tidak ditemukan.");
    }
    if ($seatData['status'] == 'booked') {
        throw new Exception("Seat sudah dibooking!");
    }

    // insert booking
    $stmt = $conn->prepare("INSERT INTO booking (flight_id, seat_no, passenger_name, passenger_phone, passenger_email)
                            VALUES (?,?,?,?,?)");
    $stmt->execute([$flight_id, $seat_no, $name, $phone, $email]);
    $booking_id = $conn->lastInsertId();

    // update seat status
    $stmt = $conn->prepare("UPDATE seat SET status='booked' WHERE flight_id = ? AND seat_no = ?");
    $stmt->execute([$flight_id, $seat_no]);

    $conn->commit();

    header("Location: show_booking.php?id=".$booking_id);
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    echo "Gagal booking: " . $e->getMessage();
}
?>
