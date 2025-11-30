<?php
header('Content-Type: application/json; charset=utf-8');
include 'connection.php';

// read JSON body or fallback to POST/GET
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = $_POST + $_GET;

$asal = trim($input['asal'] ?? '');
$tujuan = trim($input['tujuan'] ?? '');
$depart_date = trim($input['depart_date'] ?? '');

if ($asal === '' || $tujuan === '' || $depart_date === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// ensure $conn
if (!isset($conn)) {
    $conn = new mysqli('localhost', 'root', '', 'petra_airlines');
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB connection failed.']);
        exit;
    }
}

$where = [];
$params = [];

$where[] = "asal LIKE ?";
$params[] = "%{$asal}%";
$where[] = "tujuan LIKE ?";
$params[] = "%{$tujuan}%";
$where[] = "tanggal_berangkat = ?";
$params[] = $depart_date;

$sql = "SELECT id, asal, tujuan, tanggal_berangkat, jam_berangkat, jam_tiba, harga, kursi_tersedia
        FROM penerbangan
        WHERE " . implode(' AND ', $where) . "
        ORDER BY tanggal_berangkat, jam_berangkat
        LIMIT 200";

try {
    if ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception('Prepare failed');
        $ok = $stmt->execute($params);
        if (!$ok) throw new Exception('Execute failed');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception('Prepare failed');
        $types = str_repeat('s', count($params));
        $bind = array_merge([$types], $params);
        $refs = [];
        foreach ($bind as $k => $v) $refs[$k] = & $bind[$k];
        call_user_func_array([$stmt, 'bind_param'], $refs);
        if (!$stmt->execute()) throw new Exception('Execute failed');
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>