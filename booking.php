<?php
include 'connection.php';


$flight_id = 0;
if (isset($_GET['id'])) $flight_id = intval($_GET['id']);
elseif (isset($_GET['flight_id'])) $flight_id = intval($_GET['flight_id']);


if ($flight_id <= 0) {
    
    $flights = [];
    if ($conn instanceof PDO) {
        $stmt = $conn->query("SELECT id, kode_penerbangan, asal, tujuan, tanggal_berangkat, jam_berangkat, harga FROM penerbangan ORDER BY tanggal_berangkat, jam_berangkat");
        $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT id, kode_penerbangan, asal, tujuan, tanggal_berangkat, jam_berangkat, harga FROM penerbangan ORDER BY tanggal_berangkat, jam_berangkat");
        $stmt->execute();
        $res = $stmt->get_result();
        $flights = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }

    include 'header.php';
    echo '<div class="container py-4"><h3>Available flights</h3>';
    if (empty($flights)) {
        echo '<div class="alert alert-warning">No flights available.</div>';
    } else {
        echo '<div class="row g-3">';
        foreach ($flights as $f) {
            echo '<div class="col-md-6"><div class="card"><div class="card-body">';
            echo '<h5>' . htmlspecialchars($f['kode_penerbangan'] ?? '') . ' — ' . htmlspecialchars($f['asal']) . ' → ' . htmlspecialchars($f['tujuan']) . '</h5>';
            echo '<p class="mb-1">Date: ' . htmlspecialchars($f['tanggal_berangkat']) . ' • ' . htmlspecialchars($f['jam_berangkat']) . '</p>';
            echo '<p class="mb-1">Price: Rp' . number_format($f['harga'] ?? 0,0,',','.') . '</p>';
            echo '<a class="btn btn-primary" href="booking.php?id=' . (int)$f['id'] . '">Book this flight</a>';
            echo '</div></div></div>';
        }
        echo '</div>';
    }
    echo '</div>';
    include 'footer.php';
    exit;
}

// load flight securely
$flight = null;
if ($conn instanceof PDO) {
    $stmt = $conn->prepare("SELECT * FROM penerbangan WHERE id = ? LIMIT 1");
    $stmt->execute([$flight_id]);
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare("SELECT * FROM penerbangan WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $flight_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $flight = $res ? $res->fetch_assoc() : null;
    $stmt->close();
}

if (!$flight) {
    include 'header.php';
    echo '<div class="container py-4"><div class="alert alert-danger">Penerbangan tidak ditemukan.</div></div>';
    include 'footer.php';
    exit;
}

// ensure seats exist: if none, generate 1A..6F
$cnt = 0;
if ($conn instanceof PDO) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM seat WHERE flight_id = ?");
    $stmt->execute([$flight_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $cnt = intval($row['cnt'] ?? 0);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM seat WHERE flight_id = ?");
    $stmt->bind_param('i', $flight_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $cnt = intval($row['cnt'] ?? 0);
    $stmt->close();
}

if ($cnt === 0) {
    $letters = ['A','B','C','D','E','F'];
    if ($conn instanceof PDO) {
        $ins = $conn->prepare("INSERT INTO seat (flight_id, seat_no, class, status) VALUES (?, ?, ?, 'available')");
        for ($r = 1; $r <= 6; $r++) {
            foreach ($letters as $l) {
                $seatNo = $r . $l;
                $cls = ($r <= 2) ? 'business' : 'economy';
                $ins->execute([$flight_id, $seatNo, $cls]);
            }
        }
    } else {
        $ins = $conn->prepare("INSERT INTO seat (flight_id, seat_no, class, status) VALUES (?, ?, ?, 'available')");
        for ($r = 1; $r <= 6; $r++) {
            foreach ($letters as $l) {
                $seatNo = $r . $l;
                $cls = ($r <= 2) ? 'business' : 'economy';
                $ins->bind_param('iss', $flight_id, $seatNo, $cls);
                $ins->execute();
            }
        }
        $ins->close();
    }
}

// fetch seats to display
$seats = [];
if ($conn instanceof PDO) {
    $stmt = $conn->prepare("SELECT id, seat_no, status, class FROM seat WHERE flight_id = ? ORDER BY seat_no");
    $stmt->execute([$flight_id]);
    $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare("SELECT id, seat_no, status, class FROM seat WHERE flight_id = ? ORDER BY seat_no");
    $stmt->bind_param('i', $flight_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $seats = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}

include 'header.php';
?>

<div class="container py-4">
  <h2>Booking Penerbangan — <?= htmlspecialchars($flight['kode_penerbangan']) ?></h2>

  <div class="mb-3">
    <p><b>Asal:</b> <?= htmlspecialchars($flight['asal']) ?></p>
    <p><b>Tujuan:</b> <?= htmlspecialchars($flight['tujuan']) ?></p>
    <p><b>Tanggal:</b> <?= htmlspecialchars($flight['tanggal_berangkat']) ?></p>
    <p><b>Jam:</b> <?= htmlspecialchars($flight['jam_berangkat']) ?></p>
    <p><b>Harga:</b> Rp<?= number_format($flight['harga'] ?? 0, 0, ',', '.') ?></p>
  </div>

  <h5>Pilih Kursi</h5>
  <div id="seat-map" class="mb-3 d-flex flex-wrap">
    <?php foreach ($seats as $s): 
        $cls = ($s['status'] === 'available') ? 'available' : 'booked';
    ?>
      <div class="seat <?= $cls ?> me-2 mb-2 p-2 text-center"
           data-seat-id="<?= (int)$s['id'] ?>"
           data-seat-no="<?= htmlspecialchars($s['seat_no']) ?>"
           style="width:64px;border-radius:6px;cursor:<?= $cls === 'available' ? 'pointer' : 'not-allowed' ?>;">
           <div><strong><?= htmlspecialchars($s['seat_no']) ?></strong></div>
           <small class="text-muted"><?= htmlspecialchars($s['class']) ?></small>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mb-2"><strong>Selected seat:</strong> <span id="chosen-seat">-</span></div>

  <form action="./booking_action.php" method="POST">
      <input type="hidden" name="flight_id" value="<?= (int)$flight_id ?>">
      <input type="hidden" id="seat_id" name="seat_id">
      <input type="hidden" id="seat_number" name="seat_number">

      <div class="mb-2">
        <label>Nama</label>
        <input type="text" name="name" class="form-control" required>
      </div>

      <div class="row mb-3">
        <div class="col">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col">
          <label>Nomor HP</label>
          <input type="text" name="phone" class="form-control" required>
        </div>
      </div>

      <button id="btn-book" type="submit" class="btn btn-primary" disabled>Submit Booking</button>
  </form>
</div>

<script>
(function () {
  let selectedId = null;
  const seatEls = document.querySelectorAll('.seat.available');
  const seatIdInput = document.getElementById('seat_id');
  const seatNumberInput = document.getElementById('seat_number');
  const chosen = document.getElementById('chosen-seat');
  const btn = document.getElementById('btn-book');

  seatEls.forEach(el => {
    el.addEventListener('click', () => {
      if (selectedId) document.querySelector('.seat[data-seat-id="'+selectedId+'"]')?.classList.remove('border','border-primary');
      const id = el.dataset.seatId;
      const no = el.dataset.seatNo;
      selectedId = id;
      el.classList.add('border','border-primary');
      seatIdInput.value = id;
      seatNumberInput.value = no; // keep compatibility
      chosen.textContent = no;
      btn.disabled = false;
    });
  });

  // simple form check
  document.querySelector('form').addEventListener('submit', function (e) {
    if (!seatIdInput.value) {
      e.preventDefault();
      alert('Pilih kursi terlebih dahulu.');
      return false;
    }
  });
})();
</script>

<?php
include 'footer.php';
?>