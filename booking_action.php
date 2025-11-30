<?php
include 'connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    error_log('booking_action called without POST. REQUEST_METHOD=' . $_SERVER['REQUEST_METHOD'] . ' URI=' . $_SERVER['REQUEST_URI']);
    die('Invalid request method.');
}
error_log('booking_action POST keys: ' . json_encode(array_keys($_POST)) . ' session_user=' . ($_SESSION['user_id'] ?? 'NULL'));

if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to book.';
    header('Location: login.php');
    exit;
}

$flight_id = intval($_POST['flight_id'] ?? 0);
$seat_id   = intval($_POST['seat_id'] ?? 0);
$name      = trim($_POST['name'] ?? $_POST['passenger_name'] ?? '');
$email     = trim($_POST['email'] ?? $_POST['passenger_email'] ?? '');
$phone     = trim($_POST['phone'] ?? $_POST['passenger_phone'] ?? '');
$passenger_id = intval($_SESSION['user_id']);

if ($flight_id <= 0 || $seat_id <= 0 || $name === '') {
    $_SESSION['error'] = 'Invalid booking data.';
    header("Location: booking.php?flight_id={$flight_id}");
    exit;
}

// Ensure $conn exists
if (!isset($conn)) {
    $_SESSION['error'] = 'Database connection missing.';
    header("Location: booking.php?flight_id={$flight_id}");
    exit;
}

try {
    if ($conn instanceof PDO) {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT id, flight_id, status FROM seat WHERE id = ? FOR UPDATE");
        $stmt->execute([$seat_id]);
        $seat = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$seat) throw new Exception('Seat not found.');
        if ((int)$seat['flight_id'] !== $flight_id) throw new Exception('Seat does not belong to this flight.');
        if ($seat['status'] === 'booked') throw new Exception('Seat already booked.');

        $ins = $conn->prepare("INSERT INTO booking (flight_id, seat_id, passenger_id, passenger_name, passenger_phone, passenger_email) VALUES (?, ?, ?, ?, ?, ?)");
        $ok = $ins->execute([$flight_id, $seat_id, $passenger_id, $name, $phone, $email]);
        $booking_id = $conn->lastInsertId();

        if (!$ok || !$booking_id) {
            throw new Exception('Booking insert failed.');
        }

        $upd = $conn->prepare("UPDATE seat SET status = 'booked' WHERE id = ?");
        $upd->execute([$seat_id]);
        if ($upd->rowCount() === 0) {
            throw new Exception('Failed to mark seat booked.');
        }

        $upd2 = $conn->prepare("UPDATE penerbangan SET kursi_tersedia = GREATEST(kursi_tersedia - 1, 0) WHERE id = ?");
        $upd2->execute([$flight_id]);

        $conn->commit();

    } else {
        // mysqli path
        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT id, flight_id, status FROM seat WHERE id = ? FOR UPDATE");
        $stmt->bind_param('i', $seat_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $seat = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$seat) throw new Exception('Seat not found.');
        if ((int)$seat['flight_id'] !== $flight_id) throw new Exception('Seat does not belong to this flight.');
        if ($seat['status'] === 'booked') throw new Exception('Seat already booked.');

        $ins = $conn->prepare("INSERT INTO booking (flight_id, seat_id, passenger_id, passenger_name, passenger_phone, passenger_email) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param('iiisss', $flight_id, $seat_id, $passenger_id, $name, $phone, $email);
        if (!$ins->execute()) {
            $err = $ins->error;
            $ins->close();
            throw new Exception("Booking insert failed: {$err}");
        }

        if ($conn->affected_rows === 0) {
            $ins->close();
            throw new Exception('Booking insert failed (no rows affected).');
        }

        $booking_id = $conn->insert_id;
        $ins->close();

        $up = $conn->prepare("UPDATE seat SET status = 'booked' WHERE id = ?");
        $up->bind_param('i', $seat_id);
        if (!$up->execute() || $conn->affected_rows === 0) {
            $err = $conn->error;
            $up->close();
            throw new Exception("Failed to mark seat booked: {$err}");
        }
        $up->close();

        $up2 = $conn->prepare("UPDATE penerbangan SET kursi_tersedia = GREATEST(kursi_tersedia - 1, 0) WHERE id = ?");
        $up2->bind_param('i', $flight_id);
        $up2->execute();
        $up2->close();

        $conn->commit();
    }

    // success - redirect to booking detail
    header("Location: show_booking.php?id=" . (int)$booking_id);
    exit;

} catch (Exception $e) {
    // rollback + log
    if ($conn instanceof PDO) {
        if ($conn->inTransaction()) $conn->rollBack();
    } else {
        $conn->rollback();
    }
    error_log('Booking error: ' . $e->getMessage());
    // show generic order error on return
    $_SESSION['error'] = 'Order error';
    header("Location: booking.php?flight_id={$flight_id}");
    exit;
}
?>