<?php
include 'header.php';
?>
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

          <form id="searchForm" class="row g-2" onsubmit="return false;">
            <div class="col-md-6">
              <label class="form-label">From</label>
              <input id="asal" type="text" name="asal" class="form-control" placeholder="Surabaya (SUB)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">To</label>
              <input id="tujuan" type="text" name="tujuan" class="form-control" placeholder="Singapore (SIN)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Depart</label>
              <input id="depart_date" type="date" name="depart_date" class="form-control" required>
            </div>

            <div class="col-12">
              <button id="searchBtn" class="btn btn-primary w-100" type="button">Search</button>
            </div>
          </form>

          <div id="results" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
const resultsEl = document.getElementById('results');
const btn = document.getElementById('searchBtn');

function escapeHtml(s) {
  return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
}

async function doSearch() {
  resultsEl.innerHTML = '';
  btn.disabled = true;
  const asal = document.getElementById('asal').value.trim();
  const tujuan = document.getElementById('tujuan').value.trim();
  const depart_date = document.getElementById('depart_date').value;

  if (!asal || !tujuan || !depart_date) {
    resultsEl.innerHTML = '<div class="alert alert-danger">Please fill all fields</div>';
    btn.disabled = false;
    return;
  }

  resultsEl.innerHTML = '<div class="alert alert-info">Searching…</div>';

  try {
    const resp = await fetch('search_ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ asal, tujuan, depart_date })
    });

    const json = await resp.json();
    if (!resp.ok || !json.success) {
      resultsEl.innerHTML = `<div class="alert alert-danger">Error: ${escapeHtml(json.message || 'Search failed')}</div>`;
      btn.disabled = false;
      return;
    }

    const rows = json.data || [];
    if (!rows.length) {
      resultsEl.innerHTML = '<div class="alert alert-warning">No flights found</div>';
      btn.disabled = false;
      return;
    }

    let html = '<div class="table-responsive"><table class="table table-striped table-sm"><thead><tr>';
    html += '<th>Asal</th><th>Tujuan</th><th>Tanggal</th><th>Berangkat</th><th>Tiba</th><th>Harga</th><th>Kursi</th>';
    html += '</tr></thead><tbody>';
    rows.forEach(r => {
      html += '<tr>' +
        `<td>${escapeHtml(r.asal)}</td>` +
        `<td>${escapeHtml(r.tujuan)}</td>` +
        `<td>${escapeHtml(r.tanggal_berangkat)}</td>` +
        `<td>${escapeHtml(r.jam_berangkat)}</td>` +
        `<td>${escapeHtml(r.jam_tiba)}</td>` +
        `<td>${escapeHtml(new Intl.NumberFormat('id-ID').format(r.harga))}</td>` +
        `<td>${escapeHtml(String(r.kursi_tersedia))}</td>` +
      '</tr>';
    });
    html += '</tbody></table></div>';
    resultsEl.innerHTML = html;
  } catch (err) {
    resultsEl.innerHTML = `<div class="alert alert-danger">Request failed: ${escapeHtml(err.message)}</div>`;
  } finally {
    btn.disabled = false;
  }
}

btn.addEventListener('click', doSearch);

document.getElementById('searchForm').addEventListener('keyup', (ev) => {
  if (ev.key === 'Enter') doSearch();
});
</script>

<?php
include 'footer.php';
?>