<?php
  include 'header.php';
  include 'connection.php';
?>
<h1 class="text-center brand-gradient my-8 text-4xl font-bold">Search Flights</h1>  

<section id="destinations" class="py-5">
  <div class="container">
    <div class="mb-4 text-center">
      <h2 class="fw-bold">Featured destinations</h2>
      <p class="text-muted">Jelajahi kota-kota populer dengan penawaran spesial.</p>
    </div>

    <!-- rotatable slider -->
    <div class="dest-slider position-relative">
      <button class="dest-prev btn btn-sm btn-outline-secondary position-absolute top-50 start-0 translate-middle-y ms-1" aria-label="Previous">&larr;</button>
      <div class="dest-viewport overflow-hidden">
        <div class="dest-track d-flex gap-3">
          <?php
          // contoh data
          $destinations = [
            ['city'=>'Singapore','country'=>'Singapore','image'=>'image/singapore.jpg'],
            ['city'=>'Jakarta','country'=>'Indonesia','image'=>'image/jakarta.jpg'],
            ['city'=>'Tokyo','country'=>'Japan','image'=>'image/tokyo.jpg'],
            ['city'=>'Bali','country'=>'Indonesia','image'=>'image/bali.jpg'],
            ['city'=>'Yogyakarta','country'=>'Indonesia','image'=>'image/yogyakarta.jpg']
          ];
          foreach($destinations as $d): ?>
            <div class="dest-item col-6 col-md-4 flex-shrink-0" style="max-width:320px;">
              <div class="card h-100 shadow-sm">
                <img src="<?= htmlspecialchars($d['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($d['city']) ?>">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($d['city']) ?></h5>
                  <p class="card-text text-muted"><?= htmlspecialchars($d['country']) ?></p>
                  <a href="search.php" class="btn btn-outline-primary btn-sm">Cari penerbangan</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="dest-next btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-1" aria-label="Next">&rarr;</button>
    </div>
  </div>
</section>

<?php

$promoQuery = $conn->query("
    SELECT id, kode_penerbangan, asal, tujuan, tanggal_berangkat, harga 
    FROM penerbangan
    ORDER BY harga ASC
    LIMIT 3
");

$promos = $promoQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2 class="mb-4">🌟 Promo Terbaik Hari Ini</h2>
    <div class="row">
        <?php foreach($promos as $promo): ?>
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($promo['kode_penerbangan']) ?></h5>
                        <p class="card-text">
                            <?= htmlspecialchars($promo['asal']) ?> ➜ <?= htmlspecialchars($promo['tujuan']) ?><br>
                            Tanggal: <?= htmlspecialchars($promo['tanggal_berangkat']) ?><br>
                            Harga: <b>Rp<?= number_format($promo['harga'], 0, ',', '.') ?></b>
                        </p>
                        <a href="booking.php?flight_id=<?= $promo['id'] ?>" class="btn btn-primary w-100">
                            Book Now
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<style>
  /* slider styles */
  .dest-slider { --gap: 1rem; margin-bottom: 1rem; }
  .dest-viewport { width: 100%; }
  .dest-track { transition: transform .45s cubic-bezier(.2,.8,.2,1); padding-bottom: 6px; }
  .dest-item img { height: 160px; object-fit: cover; }
  /* hide controls on very small screens if desired */
  @media (max-width: 480px) {
    .dest-prev, .dest-next { display: none; }
  }
</style>

<script>
(function () {
  const track = document.querySelector('.dest-track');
  const items = track ? Array.from(track.querySelectorAll('.dest-item')) : [];
  const prevBtn = document.querySelector('.dest-prev');
  const nextBtn = document.querySelector('.dest-next');
  const viewport = document.querySelector('.dest-viewport');

  if (!track || items.length === 0 || !viewport) return;

  let index = 0;
  let timer = null;

  function getGap() {
    const g = getComputedStyle(track).getPropertyValue('gap');
    return parseFloat(g) || 0;
  }

  function itemWidth() {
    return items[0].getBoundingClientRect().width + getGap();
  }

  function visibleCount() {
    return Math.max(1, Math.floor(viewport.clientWidth / itemWidth()));
  }

  function maxIndex() {
    return Math.max(0, items.length - visibleCount());
  }

  function updateTransform() {
    const off = Math.max(0, Math.min(index, maxIndex())) * itemWidth();
    track.style.transform = `translateX(-${off}px)`;
  }

  function next() {
    if (maxIndex() === 0) { index = 0; updateTransform(); return; }
    index = (index >= maxIndex()) ? 0 : index + 1;
    updateTransform();
  }

  function prev() {
    if (maxIndex() === 0) { index = 0; updateTransform(); return; }
    index = (index <= 0) ? maxIndex() : index - 1;
    updateTransform();
  }

  if (nextBtn) nextBtn.addEventListener('click', () => { next(); resetTimer(); });
  if (prevBtn) prevBtn.addEventListener('click', () => { prev(); resetTimer(); });

  // advance when clicking any button (but skip the prev/next controls)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button');
    if (!btn) return;
    if (btn.classList.contains('dest-prev') || btn.classList.contains('dest-next')) return;
    next();
    resetTimer();
  });

  // advance on any key press unless the user is typing in an input/textarea/select
  document.addEventListener('keydown', (e) => {
    const active = document.activeElement;
    if (active && /INPUT|TEXTAREA|SELECT/.test(active.tagName)) return;
    next();
    resetTimer();
  });

  function startTimer() {
    stopTimer();
    timer = setInterval(() => next(), 3500);
  }
  function stopTimer() { if (timer) { clearInterval(timer); timer = null; } }
  function resetTimer() { startTimer(); }

  viewport.addEventListener('mouseenter', stopTimer);
  viewport.addEventListener('mouseleave', startTimer);

  window.addEventListener('resize', () => {
    // recalc and clamp
    const m = maxIndex();
    if (index > m) index = m;
    updateTransform();
  });

  updateTransform();

  // start rotating immediately (advance once and then keep auto-rotating)
  next();
  startTimer();
})();
</script>

<?php
  include 'footer.php';
?>
