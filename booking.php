<?php
include 'connection.php';
include 'header.php';

$flight_id = 0;
if (isset($_GET['id'])) $flight_id = intval($_GET['id']);
elseif (isset($_GET['flight_id'])) $flight_id = intval($_GET['flight_id']);
else $flight_id = 1; // fallback

// fetch available flights for selector
$flightsStmt = $conn->prepare("SELECT id, kode_penerbangan, asal, tujuan, tanggal_berangkat, jam_berangkat FROM penerbangan ORDER BY tanggal_berangkat, jam_berangkat");
$flightsStmt->execute();
$flights = $flightsStmt->fetchAll(PDO::FETCH_ASSOC);

// validate flight_id exists — if not set to first flight id if available
$hasFlight = false;
foreach ($flights as $f) {
    if ((int)$f['id'] === $flight_id) { $hasFlight = true; break; }
}
if (!$hasFlight && count($flights)) {
    $flight_id = (int)$flights[0]['id'];
}

/* Ambil kursi dari database */
$stmt = $conn->prepare("SELECT seat_no, status FROM seat WHERE flight_id = ? ORDER BY seat_no");
$stmt->execute([$flight_id]);
$seats = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Jika seat kosong → generate seat default */
if (empty($seats)) {
    // try get kursi_tersedia from penerbangan if exists
    $pf = $conn->prepare("SELECT kursi_tersedia FROM penerbangan WHERE id = ?");
    $pf->execute([$flight_id]);
    $frow = $pf->fetch(PDO::FETCH_ASSOC);
    $totalSeats = isset($frow['kursi_tersedia']) ? intval($frow['kursi_tersedia']) : 30; // fallback

    $perRow = 6;
    $rows = (int) ceil($totalSeats / $perRow);
    $letters = ['A','B','C','D','E','F'];
    $sqlInsert = $conn->prepare("INSERT IGNORE INTO seat (flight_id, seat_no, class, status) VALUES (?,?, 'economy', 'available')");

    for ($r = 1; $r <= $rows; $r++) {
        for ($c = 0; $c < $perRow; $c++) {
            $n = ($r - 1) * $perRow + $c + 1;
            if ($n > $totalSeats) break;
            $seatNo = $r . $letters[$c];
            $sqlInsert->execute([$flight_id, $seatNo]);
        }
    }

    // ambil ulang seat
    $stmt->execute([$flight_id]);
    $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container p-4">
    <h3>Pilih Kursi - Flight #<?= htmlspecialchars($flight_id) ?></h3>

    <!-- Flight selector -->
    <div class="mb-3">
        <label for="flightSelect" class="form-label">Select flight</label>
        <select id="flightSelect" class="form-select">
            <?php foreach ($flights as $f): ?>
                <option value="<?= (int)$f['id'] ?>" <?= ((int)$f['id'] === $flight_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['kode_penerbangan'] . ' — ' . $f['asal'] . ' → ' . $f['tujuan'] . ' • ' . $f['tanggal_berangkat'] . ' ' . $f['jam_berangkat']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="seat-map" class="mb-3">
        <?php foreach ($seats as $s): 
            $cls = ($s['status'] == 'available') ? 'available' : 'booked';
        ?>
            <div class="seat <?= $cls ?>" data-seat="<?= htmlspecialchars($s['seat_no']) ?>">
                <?= htmlspecialchars($s['seat_no']) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <style>
        .seat {
            width: 50px;
            height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 5px;
            border-radius: 6px;
            cursor: pointer;
        }
        .available { background:#d4edda; border:1px solid #28a745; }
        .booked { background:#f8d7da; border:1px solid #dc3545; cursor:not-allowed; }
        .selected { background:#cce5ff; border:1px solid #004085; }
    </style>

    <div class="mb-3"><strong>Seat Dipilih:</strong> <span id="chosen-seat">-</span></div>

    <form action="booking_action.php" method="POST">
        <!-- submit flight id as flight_id -->
        <input type="hidden" name="flight_id" id="flight_id_field" value="<?= htmlspecialchars($flight_id) ?>">
        <input type="hidden" id="seat_no" name="seat_no">

        <div class="mb-3">
            <label>Nama Penumpang</label>
            <input type="text" name="passenger_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>No Telp</label>
            <input type="text" name="passenger_phone" class="form-control">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="passenger_email" class="form-control">
        </div>

        <button id="btn-book" type="submit" class="btn btn-primary" disabled>Booking Kursi</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let selected = null;

$(".seat.available").on("click", function() {
    const seat = $(this).data("seat");

    if (selected) {
        $('.seat[data-seat="'+selected+'"]').removeClass("selected");
    }

    selected = seat;
    $(this).addClass("selected");

    $("#chosen-seat").text(seat);
    $("#seat_no").val(seat);
    $("#btn-book").prop("disabled", false);
});

// handle flight selection change — navigate to same page with selected id
$("#flightSelect").on("change", function() {
    const id = $(this).val();
    // redirect using flight_id param (supports both id and flight_id when needed)
    window.location = "booking.php?flight_id=" + encodeURIComponent(id);
});
</script>

<?php include 'footer.php'; ?>