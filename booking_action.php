<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$flight_id = isset($_POST['flight_id']) ? (int)$_POST['flight_id'] : 0;
$name      = trim($_POST['name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$seat      = trim($_POST['seat_number'] ?? '');
$seat_id   = isset($_POST['seat_id']) ? (int)$_POST['seat_id'] : 0;

if ($flight_id <= 0 || $seat_id <= 0 || $name === '' || $email === '' || $phone === '' || $seat === '') {
    die("Data tidak lengkap.");
}

try {
   
    if (!($conn instanceof PDO)) {
        throw new Exception("Koneksi database bukan PDO.");
    }

   
    $conn->beginTransaction();

   
    $sqlSeat = "SELECT id, seat_no, status 
                FROM seat 
                WHERE id = :seat_id AND flight_id = :flight_id 
                FOR UPDATE";
    $stmtSeat = $conn->prepare($sqlSeat);
    $stmtSeat->execute([
        ':seat_id'   => $seat_id,
        ':flight_id' => $flight_id
    ]);
    $seatRow = $stmtSeat->fetch(PDO::FETCH_ASSOC);

    if (!$seatRow) {
        $conn->rollBack();
        die("Kursi tidak ditemukan untuk penerbangan ini.");
    }

    if ($seatRow['status'] !== 'available') {
        $conn->rollBack();
        die("Kursi sudah dipesan orang lain. Silakan pilih kursi lain.");
    }

   
    $sqlBooking = "INSERT INTO booking (flight_id, name, email, phone, seat_number)
                   VALUES (:flight_id, :name, :email, :phone, :seat_number)";
    $stmt = $conn->prepare($sqlBooking);
    $stmt->execute([
        ':flight_id'   => $flight_id,
        ':name'        => $name,
        ':email'       => $email,
        ':phone'       => $phone,
        ':seat_number' => $seatRow['seat_no']  // pakai seat_no dari DB biar konsisten
    ]);

    $booking_id = $conn->lastInsertId();

   
    $sqlUpdateSeat = "UPDATE seat 
                      SET status = 'booked' 
                      WHERE id = :seat_id";
    $stmtUpd = $conn->prepare($sqlUpdateSeat);
    $stmtUpd->execute([':seat_id' => $seat_id]);

   
    $sqlUpdateFlight = "UPDATE penerbangan 
                        SET kursi_tersedia = CASE 
                            WHEN kursi_tersedia > 0 THEN kursi_tersedia - 1 
                            ELSE 0 
                        END
                        WHERE id = :flight_id";
    $stmtFlight = $conn->prepare($sqlUpdateFlight);
    $stmtFlight->execute([':flight_id' => $flight_id]);

   
    $conn->commit();

    header("Location: show_booking.php?id=" . $booking_id);
    exit;

} catch (Exception $e) {
    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>
