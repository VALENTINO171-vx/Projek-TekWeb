<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);
$userName = $isLoggedIn ? $_SESSION['user']['name'] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Petra Airline</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind Play CDN (untuk utility cepat) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    /* Sedikit tuning agar nuansa premium */
    .hero-bg {
      background: url('assets/img/hero.jpg') center/cover no-repeat;
      min-height: 60vh;
    }
    .backdrop {
      background: rgba(0,0,0,0.35);
    }
    .brand-gradient {
      background: linear-gradient(135deg, #0ea5e9, #1e3a8a);
      -webkit-background-clip: text; background-clip: text; color: transparent;
    }
  </style>
</head>
<body class="bg-gray-50">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
      <span class="brand-gradient">Petra Airline</span>
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
        <li class="nav-item"><a class="nav-link" href="#destinations">Destinations</a></li>
        <li class="nav-item"><a class="nav-link" href="#promos">Promotions</a></li>
        <?php if($isLoggedIn): ?>
          <li class="nav-item"><span class="nav-link">Hi, <?= htmlspecialchars($userName) ?></span></li>
          <li class="nav-item"><a class="btn btn-outline-primary" href="logout.php">Logout</a></li>
          <li class="nav-item"><a class="btn btn-primary" href="admin/flights.php">Admin</a></li>
        <?php else: ?>
          <li class="nav-item"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero + Flight Search -->
<section class="hero-bg d-flex align-items-center">
  <div class="container backdrop py-4 rounded">
    <div class="row g-4 text-white">
      <div class="col-lg-6">
        <h1 class="display-6 fw-bold">Fly better with Petra Airline</h1>
        <p class="lead">Temukan penerbangan terbaik ke destinasi favorit Anda dengan layanan kelas dunia.</p>
      </div>
      <div class="col-lg-6">
        <div class="bg-white text-gray-900 rounded p-3 shadow">
          <h5 class="mb-3 fw-semibold">Search flights</h5>
          <form id="searchForm" class="row g-2">
            <div class="col-md-6">
              <label class="form-label">From</label>
              <input type="text" name="origin" class="form-control" placeholder="Surabaya (SUB)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">To</label>
              <input type="text" name="destination" class="form-control" placeholder="Singapore (SIN)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Depart</label>
              <input type="date" name="depart_date" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Passengers</label>
              <input type="number" name="passengers" class="form-control" min="1" value="1" required>
            </div>
            <div class="col-12">
              <button class="btn btn-primary w-100" type="submit">Search</button>
            </div>
          </form>
          <div id="results" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Destinations -->
<section id="destinations" class="py-5">
  <div class="container">
    <div class="mb-4 text-center">
      <h2 class="fw-bold">Featured destinations</h2>
      <p class="text-muted">Jelajahi kota-kota populer dengan penawaran spesial.</p>
    </div>
    <div class="row g-3" id="destGrid">
      <!-- Diisi server-side atau AJAX -->
      <?php
      // contoh render cepat (nanti bisa diganti query DB Destination)
      $destinations = [
        ['city'=>'Singapore','country'=>'Singapore','image'=>'assets/img/sin.jpg'],
        ['city'=>'Jakarta','country'=>'Indonesia','image'=>'assets/img/ckg.jpg'],
        ['city'=>'Tokyo','country'=>'Japan','image'=>'assets/img/tyo.jpg'],
      ];
      foreach($destinations as $d): ?>
      <div class="col-6 col-md-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= htmlspecialchars($d['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($d['city']) ?>">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($d['city']) ?></h5>
            <p class="card-text text-muted"><?= htmlspecialchars($d['country']) ?></p>
            <a href="#search" class="btn btn-outline-primary btn-sm">Cari penerbangan</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Promotions -->
<section id="promos" class="py-5 bg-white">
  <div class="container">
    <div class="mb-4 text-center">
      <h2 class="fw-bold">Latest promotions</h2>
      <p class="text-muted">Nikmati diskon terbatas untuk rute pilihan.</p>
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <div class="p-4 rounded border">
          <h6 class="fw-semibold">SUB → SIN</h6>
          <p class="text-muted mb-2">Mulai dari Rp 1.800.000</p>
          <button class="btn btn-primary btn-sm">Book now</button>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 rounded border">
          <h6 class="fw-semibold">SUB → TYO</h6>
          <p class="text-muted mb-2">Mulai dari Rp 5.200.000</p>
          <button class="btn btn-primary btn-sm">Book now</button>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 rounded border">
          <h6 class="fw-semibold">SUB → DPS</h6>
          <p class="text-muted mb-2">Mulai dari Rp 900.000</p>
          <button class="btn btn-primary btn-sm">Book now</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="py-4 bg-gray-900 text-white">
  <div class="container d-flex flex-column flex-md-row justify-content-between gap-3">
    <div>
      <h6 class="fw-bold">Petra Airline</h6>
      <p class="text-white-50 mb-0">© <?= date('Y') ?> All rights reserved.</p>
    </div>
    <div class="d-flex gap-3">
      <a href="#" class="text-white-50 text-decoration-none">Privacy</a>
      <a href="#" class="text-white-50 text-decoration-none">Terms</a>
      <a href="#" class="text-white-50 text-decoration-none">Help</a>
    </div>
  </div>
</footer>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="loginForm">
        <div class="modal-header">
          <h5 class="modal-title">Login</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required />
          </div>
          <div id="loginMsg" class="text-danger small"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Login</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// AJAX: search flights
$('#searchForm').on('submit', function(e){
  e.preventDefault();
  $('#results').html('<div class="text-center py-3">Searching...</div>');
  $.ajax({
    url: 'api/search_flights.php',
    method: 'GET',
    data: $(this).serialize(),
    success: function(html){
      $('#results').html(html);
    },
    error: function(){
      $('#results').html('<div class="alert alert-danger">Terjadi kesalahan saat mencari penerbangan.</div>');
    }
  });
});

// AJAX: login
$('#loginForm').on('submit', function(e){
  e.preventDefault();
  $('#loginMsg').text('');
  $.ajax({
    url: 'login.php',
    method: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res){
      if(res.success){
        location.reload();
      } else {
        $('#loginMsg').text(res.message || 'Login gagal');
      }
    },
    error: function(){
      $('#loginMsg').text('Terjadi kesalahan server');
    }
  });
});
</script>
</body>
</html>