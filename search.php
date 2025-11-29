
<?php
    include 'header.php';
?>
    <h1>Search Flights</h1>

    <form method="get" action="search.php">
        <input type="text" name="asal" value="<?php echo htmlspecialchars($_GET['asal'] ?? ''); ?>" placeholder="Asal (city)"/>
        <input type="text" name="tujuan" value="<?php echo htmlspecialchars($_GET['tujuan'] ?? ''); ?>" placeholder="Tujuan (city)"/>
        <input type="date" name="tanggal" value="<?php echo htmlspecialchars($_GET['tanggal'] ?? ''); ?>" />
        <button type="submit">Search</button>
    </form>

<?php

$asal = trim($_GET['asal'] ?? '');
$tujuan = trim($_GET['tujuan'] ?? '');
$tanggal = trim($_GET['tanggal'] ?? '');

$where = [];
$params = [];

if (!isset($conn)) {
    $conn = new mysqli('localhost', 'root', '', 'petra_airlines'); // adjust creds if necessary
    if ($conn->connect_error) {
        echo '<p>Database connection error.</p>';
        include 'footer.php';
        exit;
    }
}

if ($asal !== '') {
    $where[] = "asal LIKE ?";
    $params[] = "%{$asal}%";
}
if ($tujuan !== '') {
    $where[] = "tujuan LIKE ?";
    $params[] = "%{$tujuan}%";
}
if ($tanggal !== '') {
    $where[] = "tanggal_berangkat = ?";
    $params[] = $tanggal;
}

$sql = "SELECT id, asal, tujuan, tanggal_berangkat, jam_berangkat, jam_tiba, harga, kursi_tersedia
        FROM penerbangan";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY tanggal_berangkat, jam_berangkat LIMIT 100";

if ($conn instanceof PDO) {
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $execParams = $params ?: [];
        $ok = $stmt->execute($execParams);
        if ($ok) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                echo '<table border="1" cellpadding="6" cellspacing="0">';
                echo '<thead><tr><th>Asal</th><th>Tujuan</th><th>Tanggal</th><th>Berangkat</th><th>Tiba</th><th>Harga</th><th>Kursi</th></tr></thead>';
                echo '<tbody>';
                foreach ($rows as $row) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['asal']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['tujuan']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['tanggal_berangkat']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['jam_berangkat']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['jam_tiba']) . '</td>';
                    echo '<td>' . htmlspecialchars(number_format($row['harga'], 0, ',', '.')) . '</td>';
                    echo '<td>' . (int)$row['kursi_tersedia'] . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No flights found.</p>';
            }
        } else {
            echo '<p>Search error.</p>';
        }
    } else {
        echo '<p>Search error.</p>';
    }
} else {
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $types = '';
            foreach ($params as $p) {
                $types .= 's';
            }
            $bindNames = [];
            $bindNames[] = & $types;
            foreach ($params as $i => $val) {
                $bindNames[] = & $params[$i];
            }
            call_user_func_array([$stmt, 'bind_param'], $bindNames);
        }

        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            echo '<table border="1" cellpadding="6" cellspacing="0">';
            echo '<thead><tr><th>Asal</th><th>Tujuan</th><th>Tanggal</th><th>Berangkat</th><th>Tiba</th><th>Harga</th><th>Kursi</th></tr></thead>';
            echo '<tbody>';
            while ($row = $res->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['asal']) . '</td>';
                echo '<td>' . htmlspecialchars($row['tujuan']) . '</td>';
                echo '<td>' . htmlspecialchars($row['tanggal_berangkat']) . '</td>';
                echo '<td>' . htmlspecialchars($row['jam_berangkat']) . '</td>';
                echo '<td>' . htmlspecialchars($row['jam_tiba']) . '</td>';
                echo '<td>' . htmlspecialchars(number_format($row['harga'], 0, ',', '.')) . '</td>';
                echo '<td>' . (int)$row['kursi_tersedia'] . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No flights found.</p>';
        }

        $stmt->close();
    } else {
        echo '<p>Search error.</p>';
    }
}

include 'footer.php';
?>