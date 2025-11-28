<?php
include 'config.php';

// Proses Pencarian
$hasil_pencarian = [];
$ada_pencarian = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asal = mysqli_real_escape_string($conn, $_POST['asal']);
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    
    $query = "SELECT * FROM penerbangan WHERE 1=1";
    
    if (!empty($asal)) {
        $query .= " AND asal LIKE '%$asal%'";
    }
    if (!empty($tujuan)) {
        $query .= " AND tujuan LIKE '%$tujuan%'";
    }
    if (!empty($tanggal)) {
        $query .= " AND tanggal_berangkat = '$tanggal'";
    }
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $hasil_pencarian[] = $row;
        }
        $ada_pencarian = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petra Airlines - Cari Penerbangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .search-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .btn-search {
            background: #667eea;
            color: white;
            padding: 12px 40px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            width: 100%;
        }
        .btn-search:hover {
            background: #5568d3;
            color: white;
        }
        .flight-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            transition: transform 0.3s;
        }
        .flight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .flight-code {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        .price {
            color: #667eea;
            font-size: 24px;
            font-weight: bold;
        }
        .no-result {
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="header">
            <h1>✈️ PETRA AIRLINES</h1>
            <p class="text-muted">Temukan Penerbangan Terbaik Anda</p>
        </div>

        <!-- Form Pencarian -->
        <div class="search-box">
            <h4 class="mb-4">Cari Penerbangan</h4>
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kota Asal</label>
                        <input type="text" name="asal" class="form-control" placeholder="Contoh: Surabaya">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kota Tujuan</label>
                        <input type="text" name="tujuan" class="form-control" placeholder="Contoh: Jakarta">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Berangkat</label>
                        <input type="date" name="tanggal" class="form-control">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-search">🔍 Cari Penerbangan</button>
                </div>
            </form>
        </div>

        <!-- Hasil Pencarian -->
        <?php if ($ada_pencarian): ?>
            <div>
                <h4 class="text-white mb-3">Hasil Pencarian (<?php echo count($hasil_pencarian); ?> penerbangan)</h4>
                
                <?php if (count($hasil_pencarian) > 0): ?>
                    <?php foreach ($hasil_pencarian as $flight): ?>
                        <div class="flight-card">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <span class="flight-code"><?php echo $flight['kode_penerbangan']; ?></span>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-5 text-center">
                                            <h5 class="mb-0"><?php echo $flight['asal']; ?></h5>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($flight['jam_berangkat'])); ?></small>
                                        </div>
                                        <div class="col-2 text-center">
                                            <span style="font-size: 20px;">✈️</span>
                                        </div>
                                        <div class="col-5 text-center">
                                            <h5 class="mb-0"><?php echo $flight['tujuan']; ?></h5>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($flight['jam_tiba'])); ?></small>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">📅 <?php echo date('d M Y', strtotime($flight['tanggal_berangkat'])); ?></small>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <small class="text-muted d-block">Kursi Tersedia</small>
                                    <strong><?php echo $flight['kursi_tersedia']; ?> kursi</strong>
                                </div>
                                <div class="col-md-2 text-center">
                                    <small class="text-muted d-block">Harga</small>
                                    <div class="price">Rp <?php echo number_format($flight['harga'], 0, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-result">
                        <h5>😔 Tidak ada penerbangan ditemukan</h5>
                        <p>Coba ubah kriteria pencarian Anda</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>