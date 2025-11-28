<?php
// Koneksi ke database
$servername = "localhost";
$username = "root"; // Default Laragon
$password = ""; // Default Laragon
$dbname = "petra_airlines";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data dari form
$departure = $_GET['departure'] ?? '';
$arrival = $_GET['arrival'] ?? '';
$date = $_GET['date'] ?? '';

// Query pencarian (sederhana, case-insensitive)
$sql = "SELECT * FROM flights WHERE LOWER(departure_city) LIKE LOWER('%$departure%') AND LOWER(arrival_city) LIKE LOWER('%$arrival%') AND departure_date = '$date'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - Petra Airlines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Hasil Pencarian Penerbangan</h1>
        <a href="index.php" class="btn btn-secondary mb-3">Kembali ke Pencarian</a>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No. Penerbangan</th>
                        <th>Asal</th>
                        <th>Tujuan</th>
                        <th>Tanggal Berangkat</th>
                        <th>Tanggal Tiba</th>
                        <th>Maskapai</th>
                        <th>Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['flight_number']; ?></td>
                            <td><?php echo $row['departure_city']; ?></td>
                            <td><?php echo $row['arrival_city']; ?></td>
                            <td><?php echo $row['departure_date']; ?></td>
                            <td><?php echo $row['arrival_date']; ?></td>
                            <td><?php echo $row['airline']; ?></td>
                            <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">Tidak ada penerbangan ditemukan untuk kriteria tersebut.</p>
        <?php endif; ?>
        <?php $conn->close(); ?>
    </div>
</body>
</html>