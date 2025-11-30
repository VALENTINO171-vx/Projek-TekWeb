<?php
include 'connection.php';
include 'header.php';

$flight_id = isset($_GET['flight_id']) ? intval($_GET['flight_id']) : 1;

/* Ambil kursi dari database */
$stmt = $conn->prepare("SELECT seat_no, status FROM seat WHERE flight_id = ? ORDER BY seat_no");
$stmt->execute([$flight_id]);
$seats = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Jika seat kosong → generate seat default */
if (empty($seats)) {
    $rows = ['A','B','C','D','E'];
    $cols = range(1,6);

    $sqlInsert = $conn->prepare("INSERT IGNORE INTO seat (flight_id, seat_no, class, status) VALUES (?,?, 'economy', 'available')");

    foreach ($rows as $r) {
        foreach ($cols as $c) {
            $sqlInsert->execute([$flight_id, $r.$c]);
        }
    }

    // ambil ulang seat
    $stmt->execute([$flight_id]);
    $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container p-4">
    <h3>Pilih Kursi - Flight #<?= $flight_id ?></h3>

    <div id="seat-map" class="mb-3">
        <?php 
        foreach ($seats as $s): 
            $cls = ($s['status'] == 'available') ? 'available' : 'booked';
        ?>
            <div class="seat <?= $cls ?>" data-seat="<?= $s['seat_no'] ?>">
                <?= $s['seat_no'] ?>
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
        <input type="hidden" name="flight_id" value="<?= $flight_id ?>">
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
</script>

<?php include 'footer.php'; ?>
