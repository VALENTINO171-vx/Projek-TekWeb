<?php
include 'connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// require login (booking linked to user)
if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to book a seat.';
    header('Location: login.php');
    exit;
}

$flight_id = intval($_POST['flight_id'] ?? 0);
$seat_id   = intval($_POST['seat_id'] ?? 0);
$name      = trim($_POST['passenger_name'] ?? '');
$phone     = trim($_POST['passenger_phone'] ?? '');
$email     = trim($_POST['passenger_email'] ?? '');
$passenger_id = intval($_SESSION['user_id']);

if (!$flight_id || !$seat_id || $name === '') {
    $_SESSION['error'] = 'Invalid booking data.';
    header("Location: booking.php?flight_id=" . ($flight_id ?: ''));
    exit;
}

/* Mulai transaksi */
try {
    if ($conn instanceof PDO) {
        $conn->beginTransaction();

        // lock seat row by id
        $stmt = $conn->prepare("SELECT id, flight_id, status FROM seat WHERE id = ? FOR UPDATE");
        $stmt->execute([$seat_id]);
        $seatData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$seatData) throw new Exception("Seat not found.");
        if ((int)$seatData['flight_id'] !== $flight_id) throw new Exception("Seat does not belong to this flight.");
        if ($seatData['status'] === 'booked') throw new Exception("Seat already booked.");

        // insert booking (use seat_id)
        $ins = $conn->prepare("INSERT INTO booking (flight_id, seat_id, passenger_id, passenger_name, passenger_phone, passenger_email) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$flight_id, $seat_id, $passenger_id, $name, $phone, $email]);
        $booking_id = $conn->lastInsertId();

        // mark seat booked
        $up = $conn->prepare("UPDATE seat SET status = 'booked' WHERE id = ?");
        $up->execute([$seat_id]);

        $conn->commit();

    } else {
        // mysqli
        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT id, flight_id, status FROM seat WHERE id = ? FOR UPDATE");
        $stmt->bind_param('i', $seat_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $seatData = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$seatData) throw new Exception("Seat not found.");
        if ((int)$seatData['flight_id'] !== $flight_id) throw new Exception("Seat does not belong to this flight.");
        if ($seatData['status'] === 'booked') throw new Exception("Seat already booked.");

        $ins = $conn->prepare("INSERT INTO booking (flight_id, seat_id, passenger_id, passenger_name, passenger_phone, passenger_email) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param('iiisss', $flight_id, $seat_id, $passenger_id, $name, $phone, $email);
        $ins->execute();
        $booking_id = $conn->insert_id;
        $ins->close();

        $up = $conn->prepare("UPDATE seat SET status = 'booked' WHERE id = ?");
        $up->bind_param('i', $seat_id);
        $up->execute();
        $up->close();

        $conn->commit();
    }

    header("Location: show_booking.php?id=" . (int)$booking_id);
    exit;

} catch (Exception $e) {
    if ($conn instanceof PDO) {
        if ($conn->inTransaction()) $conn->rollBack();
    } else {
        $conn->rollback();
    }
    $_SESSION['error'] = 'Booking failed: ' . $e->getMessage();
    header("Location: booking.php?flight_id=" . ($flight_id ?: ''));
    exit;
}
?>