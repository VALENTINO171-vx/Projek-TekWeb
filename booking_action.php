<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flight_id   = $_POST['flight_id'];
    $name        = $_POST['name'];
    $email       = $_POST['email'];
    $phone       = $_POST['phone'];
    $seat_number = $_POST['seat_number'];

    // Insert ke tabel booking
    $query = "INSERT INTO booking (flight_id, name, email, phone, seat_number)
              VALUES ('$flight_id', '$name', '$email', '$phone', '$seat_number')";

    if (mysqli_query($conn, $query)) {

        // Update kursi tersisa
        mysqli_query($conn, "UPDATE penerbangan
                             SET kursi_tersedia = kursi_tersedia - 1
                             WHERE id = $flight_id");

        header("Location: show_booking.php?id=" . mysqli_insert_id($conn));
        exit;
    } else {
        echo "Gagal insert booking: " . mysqli_error($conn);
    }
}
?>
