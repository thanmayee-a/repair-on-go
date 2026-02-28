<?php
require 'db.php';
header('Content-Type: application/json');
$request_id = intval($_GET['request_id'] ?? 0);

if(!$request_id){ echo json_encode(null); exit; }

$q = "SELECT r.*, m.name as mech_name, m.lat as mech_lat, m.lng as mech_lng FROM requests r LEFT JOIN mechanics m ON r.mechanic_id = m.id WHERE r.id = ?";
$stmt = $conn->prepare($q);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
echo json_encode($row);
