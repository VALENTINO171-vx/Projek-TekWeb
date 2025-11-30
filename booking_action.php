<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$flight_id = $_POST['flight_id'] ?? '';
$name      = $_POST['name'] ?? '';
$email     = $_POST['email'] ?? '';
$phone     = $_POST['phone'] ?? '';
$seat      = $_POST['seat_number'] ?? '';

if ($flight_id == '' || $name == '' || $email == '' || $phone == '' || $seat == '') {
    die("Data tidak lengkap.");
}

try {

    $sql = "INSERT INTO booking (flight_id, name, email, phone, seat_number)
            VALUES (:flight_id, :name, :email, :phone, :seat_number)";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':flight_id', $flight_id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':seat_number', $seat);

    $stmt->execute();

    $booking_id = $conn->lastInsertId();

    header("Location: show_booking.php?id=" . $booking_id);
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
